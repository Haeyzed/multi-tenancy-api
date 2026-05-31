#Requires -Version 5.1
<#
.SYNOPSIS
    Removes the Laravel queue worker Windows Scheduled Task.
#>

$ErrorActionPreference = 'Stop'

$TaskName = 'LaravelQueueWorker-multi-tenancy-api'

schtasks /Query /TN $TaskName 2>$null | Out-Null

if ($LASTEXITCODE -ne 0) {
    Write-Host "Task not found: $TaskName"
    exit 0
}

schtasks /Delete /TN $TaskName /F | Out-Null
Write-Host "Removed scheduled task: $TaskName"
