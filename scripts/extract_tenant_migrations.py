#!/usr/bin/env python3
"""Extract tenant migration PHP files from migration.txt."""

import re
from pathlib import Path

SOURCE = Path(r"c:\Users\IT\Downloads\migration.txt")
OUTPUT = Path(r"c:\Users\IT\Herd\multi-tenancy-api\database\migrations\tenant")

content = SOURCE.read_text(encoding="utf-8")

pattern = re.compile(
    r'with open\(f"\{output_dir\}/([^"]+\.php)", "w"\) as f:\s*\n\s*f\.write\((\w+)\)',
    re.MULTILINE,
)

var_pattern = re.compile(
    r'^(\w+_migration)\s*=\s*r"""(<\?php[\s\S]*?)\n"""',
    re.MULTILINE,
)

variables: dict[str, str] = {}
for match in var_pattern.finditer(content):
    variables[match.group(1)] = match.group(2)

def normalize_php(php: str) -> str:
    lines = php.splitlines()
    cleaned: list[str] = []
    past_php_open = False
    for line in lines:
        if line.strip() == '"""':
            continue
        if line.startswith("<?php"):
            cleaned.append(line)
            past_php_open = True
            continue
        if past_php_open and line.startswith("//"):
            continue
        past_php_open = True
        cleaned.append(line)

    php = "\n".join(cleaned)

    if "Run the migrations." not in php:
        php = php.replace(
            "    public function up(): void",
            "    /**\n     * Run the migrations.\n     */\n    public function up(): void",
        )
        php = php.replace(
            "    public function down(): void",
            "    /**\n     * Reverse the migrations.\n     */\n    public function down(): void",
        )

    if not php.endswith("\n"):
        php += "\n"

    return php

OUTPUT.mkdir(parents=True, exist_ok=True)

written: list[str] = []
missing: list[str] = []

for match in pattern.finditer(content):
    filename = match.group(1)
    var_name = match.group(2)
    if var_name not in variables:
        missing.append(f"{filename} -> {var_name}")
        continue
    php = normalize_php(variables[var_name])
    (OUTPUT / filename).write_text(php, encoding="utf-8", newline="\n")
    written.append(filename)

print(f"Extracted {len(written)} migration files to {OUTPUT}")
if missing:
    print(f"Missing {len(missing)} variables:")
    for item in missing:
        print(f"  - {item}")

expected = {f"2026_01_01_{100000 + i:06d}" for i in range(1, 157)}
found = {f.split("_create_")[0] for f in written if f.startswith("2026_01_01_100")}
# simpler check
nums = sorted(
    int(re.search(r"100(\d{3})", f).group(1))
    for f in written
    if re.search(r"100(\d{3})", f)
)
if nums:
    gaps = [n for n in range(nums[0], nums[-1] + 1) if n not in nums]
    if gaps:
        print(f"Sequence gaps: {gaps}")
