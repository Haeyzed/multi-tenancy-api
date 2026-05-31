#Requires -Version 5.1
<#
.SYNOPSIS
    Starts the queue worker scheduled task on demand.
#>

$ErrorActionPreference = 'Stop'

$TaskName = 'LaravelQueueWorker-multi-tenancy-api'

Start-ScheduledTask -TaskName $TaskName
Write-Host "Started scheduled task: $TaskName"
