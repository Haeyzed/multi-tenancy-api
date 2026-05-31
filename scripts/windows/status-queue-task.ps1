#Requires -Version 5.1
<#
.SYNOPSIS
    Shows status of the queue worker scheduled task.
#>

$ErrorActionPreference = 'Stop'

$TaskName = 'LaravelQueueWorker-multi-tenancy-api'

$task = Get-ScheduledTask -TaskName $TaskName -ErrorAction SilentlyContinue

if (-not $task) {
    Write-Host "Task not found: $TaskName"
    exit 1
}

$info = Get-ScheduledTaskInfo -TaskName $TaskName

Write-Host "Task:        $TaskName"
Write-Host "State:       $($task.State)"
Write-Host "Last Run:    $($info.LastRunTime)"
Write-Host "Last Result: $($info.LastTaskResult)"
Write-Host "Next Run:    $($info.NextRunTime)"
Write-Host "Description: $($task.Description)"
