#Requires -Version 5.1
<#
.SYNOPSIS
    Runs the Laravel queue worker for multi-tenancy-api.
.DESCRIPTION
    Started by the Windows Scheduled Task "LaravelQueueWorker-multi-tenancy-api".
    Restarts the worker after exit (e.g. max-time reached) with a short delay.
#>

$ErrorActionPreference = 'Stop'

$ProjectRoot = 'C:\Users\amuibi\Herd\multi-tenancy-api'
$LogDir = Join-Path $ProjectRoot 'storage\logs'
$LogFile = Join-Path $LogDir 'queue-worker.log'
$PhpCandidates = @(
    'C:\Users\amuibi\.config\herd\bin\php.exe',
    'C:\Users\amuibi\.config\herd\bin\php.bat'
)

function Write-Log {
    param([string] $Message)
    $line = '[{0}] {1}' -f (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'), $Message
    Add-Content -Path $LogFile -Value $line -Encoding UTF8
}

function Resolve-Php {
    foreach ($candidate in $PhpCandidates) {
        if (Test-Path $candidate) {
            return $candidate
        }
    }

    $fromPath = Get-Command php -ErrorAction SilentlyContinue

    if ($fromPath) {
        return $fromPath.Source
    }

    throw 'PHP executable not found. Install Laravel Herd or add PHP to PATH.'
}

if (-not (Test-Path $ProjectRoot)) {
    throw "Project directory not found: $ProjectRoot"
}

if (-not (Test-Path $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
}

$php = Resolve-Php
Set-Location $ProjectRoot

Write-Log 'Queue worker script started.'
Write-Log "PHP: $php"
Write-Log "Project: $ProjectRoot"

while ($true) {
    Write-Log 'Starting queue:work...'

    try {
        & $php artisan queue:work `
            --sleep=3 `
            --tries=3 `
            --timeout=90 `
            --max-time=3600 `
            --memory=128 `
            2>&1 | ForEach-Object {
                $text = $_.ToString()
                Write-Log $text
                Write-Output $text
            }

        $exitCode = $LASTEXITCODE
        Write-Log "queue:work exited with code $exitCode. Restarting in 5 seconds..."
    } catch {
        Write-Log "queue:work error: $($_.Exception.Message). Restarting in 5 seconds..."
    }

    Start-Sleep -Seconds 5
}
