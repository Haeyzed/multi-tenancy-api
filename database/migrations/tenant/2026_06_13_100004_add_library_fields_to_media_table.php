<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'folder_id')) {
                $table->foreignId('folder_id')->nullable()->after('id')->constrained('media_library_folders')->nullOnDelete();
            }

            if (! Schema::hasColumn('media', 'title')) {
                $table->string('title')->nullable()->after('name');
            }

            if (! Schema::hasColumn('media', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('title');
            }

            if (! Schema::hasColumn('media', 'uploaded_by')) {
                $table->foreignUuid('uploaded_by')->nullable()->after('alt_text')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['folder_id']);
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['folder_id', 'title', 'alt_text', 'uploaded_by']);
        });
    }
};
