# Windows Queue Worker

Scheduled Task setup for `php artisan queue:work` on this Laravel project.

## Install (requires Administrator)

Right-click **PowerShell → Run as administrator**, then:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File "C:\Users\amuibi\Herd\multi-tenancy-api\scripts\windows\install-queue-task-admin.ps1"
```

Or run `install-queue-task-admin.ps1` and approve the UAC prompt.

## Scripts

| File | Purpose |
|------|---------|
| `queue-worker.ps1` | Runs `queue:work` in a restart loop; logs to `storage/logs/queue-worker.log` |
| `laravel-queue-worker.task.xml` | Task Scheduler definition (imported by install scripts) |
| `install-queue-task-admin.ps1` | Registers the task (elevated) |
| `install-queue-task.ps1` | Non-elevated install (falls back with instructions if denied) |
| `start-queue-task.ps1` | Start the task on demand |
| `status-queue-task.ps1` | Show task state and last run |
| `uninstall-queue-task.ps1` | Remove the scheduled task |

## Task configuration

| Setting | Value |
|---------|-------|
| **Task name** | `LaravelQueueWorker-multi-tenancy-api` |
| **Trigger** | At user logon |
| **Command** | `powershell.exe` → `queue-worker.ps1` |
| **Working directory** | `C:\Users\amuibi\Herd\multi-tenancy-api` |
| **PHP** | Laravel Herd (`~\.config\herd\bin\php.exe`) |
| **Queue** | `database` (from `.env`) |
| **Worker flags** | `--sleep=3 --tries=3 --timeout=90 --max-time=3600 --memory=128` |
| **Restart** | Script loops every 5s; Task Scheduler restarts on failure (1 min, 999 times) |
| **Log file** | `storage/logs/queue-worker.log` |

## Manual commands

```powershell
# Start now
schtasks /Run /TN "LaravelQueueWorker-multi-tenancy-api"

# Check status
powershell -File scripts\windows\status-queue-task.ps1

# View log
Get-Content storage\logs\queue-worker.log -Tail 50 -Wait

# Uninstall
powershell -File scripts\windows\uninstall-queue-task.ps1
```

## Verify in Task Scheduler

1. Open **Task Scheduler** (`taskschd.msc`)
2. Go to **Task Scheduler Library**
3. Find **LaravelQueueWorker-multi-tenancy-api**
