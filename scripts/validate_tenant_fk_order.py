#!/usr/bin/env python3
import re
from pathlib import Path

tenant = Path(r"c:\Users\IT\Herd\multi-tenancy-api\database\migrations\tenant")
files = sorted(tenant.glob("*.php"))
created: dict[str, str] = {}
issues: list[tuple[str, str, str]] = []

for f in files:
    text = f.read_text(encoding="utf-8")
    match = re.search(r"Schema::create\('([^']+)'", text)
    if match:
        created[match.group(1)] = f.name
    for ref in re.findall(r"->constrained\('([^']+)'", text):
        if ref not in created:
            issues.append((f.name, ref, "table not created yet"))
        elif created[ref] > f.name:
            issues.append((f.name, ref, f"created in {created[ref]} after {f.name}"))

print(f"Tables: {len(created)}, FK order issues: {len(issues)}")
for item in issues:
    print(item)
