# PHP-Syntaxprüfung aller Theme-Dateien (php -l).
# Aufruf: pwsh dev/lint.ps1   – Exit-Code 1 bei Fehlern.

$php = 'C:\wamp64\bin\php\php8.4.15\php.exe'
if (-not (Test-Path $php)) {
    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if ($null -eq $cmd) {
        Write-Error "PHP nicht gefunden ($php und kein php im PATH)."
        exit 1
    }
    $php = $cmd.Source
}

$root  = Split-Path -Parent $PSScriptRoot
$theme = Join-Path $root 'open3'
$files = Get-ChildItem -Path $theme -Recurse -Filter *.php
$errors = 0

foreach ($file in $files) {
    $output = & $php -l $file.FullName 2>&1
    if ($LASTEXITCODE -ne 0) {
        $errors++
        Write-Host ($output -join "`n") -ForegroundColor Red
    }
}

Write-Host ("{0} Dateien geprüft, {1} Fehler ({2})." -f $files.Count, $errors, $php)
if ($errors -gt 0) {
    exit 1
}
exit 0
