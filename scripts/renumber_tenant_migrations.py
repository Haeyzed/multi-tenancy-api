#!/usr/bin/env python3
"""Renumber tenant migrations 100007+ up by 1 and insert permission at 100007."""

from pathlib import Path

TENANT = Path(r"c:\Users\IT\Herd\multi-tenancy-api\database\migrations\tenant")

permission = TENANT / "2026_05_30_230131_create_permission_tables.php"
temp = TENANT / "_temp_permission_tables.php"

if permission.exists():
    permission.rename(temp)
    print("Staged permission migration")

for num in range(156, 6, -1):
    old_num = f"100{num:03d}"
    new_num = f"100{num + 1:03d}"
    for path in list(TENANT.glob(f"2026_01_01_{old_num}_*.php")):
        new_name = path.name.replace(old_num, new_num, 1)
        path.rename(TENANT / new_name)
        print(f"Renamed {path.name} -> {new_name}")

if temp.exists():
    temp.rename(TENANT / "2026_01_01_100007_create_permission_tables.php")
    print("Placed permission migration at 100007")

print("Renumber complete.")
