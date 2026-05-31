#Requires -Version 5.1
<#
.SYNOPSIS
    Installs the queue worker scheduled task (requests Administrator elevation).
#>

$ErrorActionPreference = 'Stop'

if (-not ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole(
        [Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Host 'Requesting Administrator privileges...'
    Start-Process powershell.exe -Verb RunAs -ArgumentList @(
        '-NoProfile',
        '-ExecutionPolicy', 'Bypass',
        '-File', $PSCommandPath
    )
    exit 0
}

$ProjectRoot = 'C:\Users\amuibi\Herd\multi-tenancy-api'
$TaskName = 'LaravelQueueWorker-multi-tenancy-api'
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
    throw "Failed to register scheduled task."
}

Write-Host ''
Write-Host 'Scheduled task installed successfully.'
Write-Host "  Name:    $TaskName"
Write-Host "  Trigger: At logon"
Write-Host "  Worker:  $ProjectRoot\scripts\windows\queue-worker.ps1"
Write-Host "  Log:     $ProjectRoot\storage\logs\queue-worker.log"
Write-Host ''
Write-Host 'Starting task now...'
schtasks /Run /TN $TaskName
Write-Host ''
schtasks /Query /TN $TaskName /V /FO LIST
