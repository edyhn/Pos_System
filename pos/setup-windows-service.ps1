param(
    [switch]$Remove
)

$projectPath = Split-Path -Parent $MyInvocation.MyCommand.Path
$phpPath = "php"
$queueCommand = "php artisan queue:work --sleep=3 --tries=3 --max-time=3600"
$schedulerCommand = "php artisan schedule:work"

function Create-ScheduledTask($name, $command, $workingDir) {
    $action = New-ScheduledTaskAction -Execute "cmd.exe" -Argument "/c cd /d `"$workingDir`" && $command"
    $trigger = New-ScheduledTaskTrigger -AtStartup -RandomDelay "00:01:00"
    $principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
    $settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RestartCount 3 -RestartInterval (New-TimeSpan -Minutes 1)

    Register-ScheduledTask -TaskName $name -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Force
    Start-ScheduledTask -TaskName $name

    Write-Host "[OK] $name - Task created and started."
}

function Remove-ScheduledTask($name) {
    if (Get-ScheduledTask -TaskName $name -ErrorAction SilentlyContinue) {
        Stop-ScheduledTask -TaskName $name -ErrorAction SilentlyContinue
        Unregister-ScheduledTask -TaskName $name -Confirm:$false
        Write-Host "[OK] $name - Task removed."
    } else {
        Write-Host "[SKIP] $name - Task not found."
    }
}

if ($Remove) {
    Remove-ScheduledTask "POS Queue Worker"
    Remove-ScheduledTask "POS Scheduler"
} else {
    Write-Host "Setting up POS Windows Services..."
    Create-ScheduledTask "POS Queue Worker" $queueCommand $projectPath
    Create-ScheduledTask "POS Scheduler" $schedulerCommand $projectPath
    Write-Host "Done. Run with: .\setup-windows-service.ps1 -Remove"
}
