#Requires -Version 5.1
<#
.SYNOPSIS
    Registers the Laravel queue worker Windows Scheduled Task.
.NOTES
    Requires Administrator. If access is denied, run install-queue-task-admin.ps1 instead.
#>

$ErrorActionPreference = 'Stop'

$TaskName = 'LaravelQueueWorker-multi-tenancy-api'
$ProjectRoot = 'C:\Users\amuibi\Herd\multi-tenancy-api'
$TaskXml = Join-Path $ProjectRoot 'scripts\windows\laravel-queue-worker.task.xml'

if (-not (Test-Path $TaskXml)) {
    throw "Task XML not found: $TaskXml"
}

schtasks /Query /TN $TaskName 2>$null | Out-Null
if ($LASTEXITCODE -eq 0) {
    schtasks /Delete /TN $TaskName /F | Out-Null
    Write-Host "Removed existing task: $TaskName"
}

schtasks /Create /TN $TaskName /XML $TaskXml /F

if ($LASTEXITCODE -ne 0) {
    Write-Host ''
    Write-Host 'Access denied. Run this as Administrator:' -ForegroundColor Yellow
    Write-Host "  powershell -ExecutionPolicy Bypass -File `"$ProjectRoot\scripts\windows\install-queue-task-admin.ps1`"" -ForegroundColor Yellow
    exit 1
}

Write-Host "Scheduled task registered: $TaskName"
Write-Host "  Trigger: At logon"
Write-Host "  Script:  $ProjectRoot\scripts\windows\queue-worker.ps1"
Write-Host "  Log:     $ProjectRoot\storage\logs\queue-worker.log"
