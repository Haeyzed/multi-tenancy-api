<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Generate tenant Eloquent models from tenant migration schemas.
 */
class GenerateTenantModelsCommand extends Command
{
    protected $signature = 'tenant:generate-models {--force : Overwrite existing model files}';

    protected $description = 'Generate App\\Models\\Tenant models from database/migrations/tenant';

    /**
     * @var list<string>
     */
    private const SKIP_TABLES = [
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'users',
        'activity_log',
        'ticket_tag_pivot',
        'taggables',
    ];

    /**
     * @var array<string, string>
     */
    private const CLASS_OVERRIDES = [
        'inventory' => 'Inventory',
        'knowledge_base' => 'KnowledgeBaseArticle',
        'goods_receipt_notes' => 'GoodsReceiptNote',
        'performance_review_criteria' => 'PerformanceReviewCriterion',
        'employee_education' => 'EmployeeEducation',
        'employee_work_experience' => 'EmployeeWorkExperience',
        'media' => 'Media',
    ];

    /**
     * @var list<string>
     */
    private const EXISTING_MODELS = [
        'User',
        'Role',
        'Permission',
        'Activity',
    ];

    public function handle(): int
    {
        $migrationPath = database_path('migrations/tenant');
        $modelPath = app_path('Models/Tenant');
        $files = glob($migrationPath.'/*.php') ?: [];
        sort($files);
        $modelMap = $this->buildModelMap($files);

        $generated = 0;
        $skipped = 0;

        foreach ($files as $file) {
            if (str_contains($file, '_add_') && str_contains($file, '_foreign_keys_')) {
                continue;
            }

            $content = file_get_contents($file);

            if ($content === false || ! preg_match("/Schema::create\('([^']+)'/", $content, $tableMatch)) {
                continue;
            }

            $table = $tableMatch[1];

            if (in_array($table, self::SKIP_TABLES, true)) {
                $skipped++;

                continue;
            }

            $class = self::CLASS_OVERRIDES[$table] ?? Str::studly(Str::singular($table));

            if (in_array($class, self::EXISTING_MODELS, true)) {
                $skipped++;

                continue;
            }

            $target = $modelPath.'/'.$class.'.php';

            if (is_file($target) && ! $this->option('force')) {
                $skipped++;

                continue;
            }

            $schema = $this->parseSchema($content);
            $code = $this->buildModel($table, $class, $schema, $modelMap);

            if ($table === 'media') {
                $code = $this->buildMediaModel();
            }

            file_put_contents($target, $code);
            $generated++;
            $this->line("Generated {$class}");
        }

        $this->info("Done. Generated {$generated}, skipped {$skipped}.");

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $files
     * @return array<string, string> table => class
     */
    private function buildModelMap(array $files): array
    {
        $map = [
            'users' => 'User',
        ];

        foreach ($files as $file) {
            if (str_contains($file, '_add_') && str_contains($file, '_foreign_keys_')) {
                continue;
            }

            $content = file_get_contents($file);

            if ($content === false || ! preg_match("/Schema::create\('([^']+)'/", $content, $tableMatch)) {
                continue;
            }

            $table = $tableMatch[1];

            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            $map[$table] = self::CLASS_OVERRIDES[$table] ?? Str::studly(Str::singular($table));
        }

        return $map;
    }

    /**
     * @return array{
     *     columns: list<array{name: string, doc_type: string, cast: string|null, fillable: bool}>,
     *     uses_uuid: bool,
     *     soft_deletes: bool,
     *     foreign_keys: list<array{column: string, model: string}>
     * }
     */
    private function parseSchema(string $content): array
    {
        $usesUuid = (bool) preg_match('/\$table->uuid\([\'"]id[\'"]\)->primary\(\)/', $content);
        $softDeletes = str_contains($content, '->softDeletes(');
        $columns = [];
        $foreignKeys = [];

        preg_match_all('/\$table->([a-zA-Z]+)\(([^)]*)\)([^;]*);/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $method = $match[1];
            $args = $match[2];
            $chain = $match[3] ?? '';

            if (in_array($method, ['index', 'unique', 'foreign', 'primary', 'comment'], true)) {
                continue;
            }

            if ($method === 'morphs') {
                $base = trim($args, "'\"");
                $columns[] = ['name' => "{$base}_type", 'doc_type' => 'string', 'cast' => null, 'fillable' => true];
                $columns[] = ['name' => "{$base}_id", 'doc_type' => 'int', 'cast' => null, 'fillable' => true];

                continue;
            }

            if ($method === 'nullableTimestamps') {
                $columns[] = ['name' => 'created_at', 'doc_type' => 'Carbon|null', 'cast' => 'datetime', 'fillable' => false];
                $columns[] = ['name' => 'updated_at', 'doc_type' => 'Carbon|null', 'cast' => 'datetime', 'fillable' => false];

                continue;
            }

            if ($method === 'timestamps') {
                $columns[] = ['name' => 'created_at', 'doc_type' => 'Carbon|null', 'cast' => 'datetime', 'fillable' => false];
                $columns[] = ['name' => 'updated_at', 'doc_type' => 'Carbon|null', 'cast' => 'datetime', 'fillable' => false];

                continue;
            }

            if ($method === 'softDeletes') {
                $columns[] = ['name' => 'deleted_at', 'doc_type' => 'Carbon|null', 'cast' => 'datetime', 'fillable' => false];

                continue;
            }

            if ($method === 'rememberToken') {
                $columns[] = ['name' => 'remember_token', 'doc_type' => 'string|null', 'cast' => null, 'fillable' => false];

                continue;
            }

            if (! preg_match('/^[\'"]([^\'"]+)[\'"]/', $args, $nameMatch)) {
                if ($method === 'id') {
                    $columns[] = ['name' => 'id', 'doc_type' => 'int', 'cast' => null, 'fillable' => false];
                }

                continue;
            }

            $name = $nameMatch[1];
            [$docType, $cast] = $this->mapColumnType($method, $chain);

            if (str_contains($chain, '->nullable(') && ! str_contains($docType, 'null')) {
                $docType .= '|null';
            }

            $fillable = ! in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'], true);

            $columns[] = [
                'name' => $name,
                'doc_type' => $docType,
                'cast' => $cast,
                'fillable' => $fillable,
            ];

            if (
                str_ends_with($name, '_id')
                && $name !== 'id'
                && ! str_ends_with($name, '_type')
                && in_array($method, ['foreignId', 'foreignUuid', 'unsignedBigInteger', 'uuid'], true)
            ) {
                $related = Str::studly(Str::singular(substr($name, 0, -3)));
                $foreignKeys[] = ['column' => $name, 'model' => $related];
            }
        }

        return [
            'columns' => $columns,
            'uses_uuid' => $usesUuid,
            'soft_deletes' => $softDeletes,
            'foreign_keys' => $foreignKeys,
        ];
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    private function mapColumnType(string $method, string $chain): array
    {
        return match ($method) {
            'uuid', 'foreignUuid' => ['string', null],
            'id', 'foreignId', 'unsignedBigInteger', 'bigInteger', 'unsignedInteger', 'integer', 'smallInteger', 'tinyInteger' => ['int', null],
            'boolean' => ['bool', 'boolean'],
            'json' => ['array<string, mixed>|null', 'array'],
            'decimal', 'float', 'double' => ['string', 'decimal:2'],
            'timestamp', 'dateTime', 'dateTimeTz' => ['Carbon|null', 'datetime'],
            'date' => ['Carbon|null', 'date'],
            'enum' => ['string', null],
            'text', 'mediumText', 'longText', 'string', 'char' => ['string|null', null],
            default => ['mixed', null],
        };
    }

    /**
     * @param  array{
     *     columns: list<array{name: string, doc_type: string, cast: string|null, fillable: bool}>,
     *     uses_uuid: bool,
     *     soft_deletes: bool,
     *     foreign_keys: list<array{column: string, model: string}>
     * }  $schema
     * @param  array<string, string>  $modelMap
     */
    private function buildModel(string $table, string $class, array $schema, array $modelMap): string
    {
        $description = ucfirst(str_replace('_', ' ', $table)).' stored in the tenant database.';
        $uses = ['HasFactory'];
        $imports = [
            'use Illuminate\Database\Eloquent\Builder;',
            'use Illuminate\Database\Eloquent\Factories\HasFactory;',
            'use Illuminate\Database\Eloquent\Relations\BelongsTo;',
            'use Illuminate\Support\Carbon;',
        ];

        if ($schema['uses_uuid']) {
            $uses[] = 'HasUuids';
            $imports[] = 'use Illuminate\Database\Eloquent\Concerns\HasUuids;';
        }

        if ($schema['soft_deletes']) {
            $uses[] = 'SoftDeletes';
            $imports[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
        }

        $properties = array_map(
            fn (array $column) => " * @property {$column['doc_type']} \${$column['name']}",
            $schema['columns'],
        );
        $propertiesBlock = $properties !== [] ? "\n".implode("\n", $properties) : '';

        $fillable = array_values(array_map(
            fn (array $column) => $column['name'],
            array_filter($schema['columns'], fn (array $column) => $column['fillable']),
        ));

        $casts = array_filter(array_combine(
            array_column($schema['columns'], 'name'),
            array_column($schema['columns'], 'cast'),
        ));

        unset($casts['id']);

        $castsCode = '';

        if ($casts !== []) {
            $castLines = [];

            foreach ($casts as $name => $cast) {
                if ($cast !== null) {
                    $castLines[] = "            '{$name}' => '{$cast}',";
                }
            }

            if ($castLines !== []) {
                $castsCode = "\n    protected function casts(): array\n    {\n        return [\n"
                    .implode("\n", $castLines)
                    ."\n        ];\n    }\n";
            }
        }

        $uuidConfig = '';

        if ($schema['uses_uuid']) {
            $uuidConfig = "\n    public \$incrementing = false;\n\n    protected \$keyType = 'string';\n";
        }

        $relations = '';

        foreach ($schema['foreign_keys'] as $fk) {
            if (! in_array($fk['model'], $modelMap, true) && ! in_array($fk['model'], self::EXISTING_MODELS, true)) {
                continue;
            }

            $method = Str::camel(substr($fk['column'], 0, -3));
            $relations .= "\n    /**\n     * Related {$fk['model']}.\n     */\n";
            $relations .= "    public function {$method}(): BelongsTo\n    {\n";
            $relations .= "        return \$this->belongsTo({$fk['model']}::class);\n    }\n";
        }

        $searchable = array_values(array_filter(
            $schema['columns'],
            fn (array $column) => in_array($column['name'], ['name', 'title', 'email', 'slug', 'sku', 'order_number', 'code'], true),
        ));

        $searchScope = '';

        if ($searchable !== []) {
            $searchScope = "\n    /**\n     * Scope a query by common searchable columns.\n     */\n";
            $searchScope .= "    public function scopeSearch(Builder \$query, ?string \$search): void\n    {\n";
            $searchScope .= "        \$query->when(\$search, function (Builder \$q, string \$search) {\n";
            $searchScope .= "            \$q->where(function (Builder \$inner) use (\$search) {\n";

            foreach ($searchable as $index => $column) {
                if ($index === 0) {
                    $searchScope .= "                \$inner->where('{$column['name']}', 'like', \"%{\$search}%\")\n";
                } else {
                    $searchScope .= "                    ->orWhere('{$column['name']}', 'like', \"%{\$search}%\")\n";
                }
            }

            $searchScope .= "            );\n        });\n    }\n";
        }

        $fillableCode = implode("\n", array_map(fn (string $item) => "        '{$item}',", $fillable));
        $usesLine = implode(', ', $uses);
        $importsBlock = implode("\n", array_unique($imports));
        $methodDoc = $searchScope !== '' ? "\n * @method static Builder|{$class} search(?string \$search)" : '';

        return <<<PHP
<?php

declare(strict_types=1);

namespace App\Models\Tenant;

{$importsBlock}

/**
 * {$description}{$propertiesBlock}{$methodDoc}
 */
class {$class} extends TenantModel
{
    use {$usesLine};

    protected \$table = '{$table}';
{$uuidConfig}
    /**
     * @var list<string>
     */
    protected \$fillable = [
{$fillableCode}
    ];
{$castsCode}{$relations}{$searchScope}}

PHP;
    }

    private function buildMediaModel(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * Media library file attached to a tenant model.
 *
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string|null $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string|null $mime_type
 * @property string $disk
 * @property string|null $conversions_disk
 * @property int $size
 * @property array<string, mixed> $manipulations
 * @property array<string, mixed> $custom_properties
 * @property array<string, mixed> $generated_conversions
 * @property array<string, mixed> $responsive_images
 * @property int|null $order_column
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Media extends SpatieMedia
{
    //
}

PHP;
    }
}
