# PowerShell script to resolve Git merge conflicts
# This script accepts incoming changes (removes HEAD section, keeps incoming)

$ErrorActionPreference = "Stop"

# Get all files with conflict markers
$conflictFiles = Get-ChildItem -Recurse -File | Where-Object {
    (Get-Content $_.FullName -Raw -ErrorAction SilentlyContinue) -match '<<<<<<< HEAD'
}

$resolvedCount = 0
$failedFiles = @()

foreach ($file in $conflictFiles) {
    try {
        Write-Host "Processing: $($file.FullName)"
        $content = Get-Content $file.FullName -Raw
        
        # Pattern to match conflict blocks
        # <<<<<<< HEAD ... ======= (keep this) >>>>>>> hash
        $pattern = '(?s)<<<<<<< HEAD.*?=======\r?\n(.*?)>>>>>>> [a-f0-9]+'
        
        if ($content -match $pattern) {
            # Replace conflict blocks with incoming changes
            $newContent = $content -replace $pattern, '$1'
            
            # Write back to file
            Set-Content -Path $file.FullName -Value $newContent -NoNewline
            
            $resolvedCount++
            Write-Host "  Resolved conflicts in: $($file.Name)" -ForegroundColor Green
        }
    }
    catch {
        $failedFiles += $file.FullName
        Write-Host "  Failed to process: $($file.Name)" -ForegroundColor Red
        Write-Host "    Error: $_" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "================================================" -ForegroundColor Cyan
Write-Host "Resolution Summary:" -ForegroundColor Cyan
Write-Host "  Total files resolved: $resolvedCount" -ForegroundColor Green
if ($failedFiles.Count -gt 0) {
    Write-Host "  Failed files: $($failedFiles.Count)" -ForegroundColor Red
    foreach ($failed in $failedFiles) {
        Write-Host "    - $failed" -ForegroundColor Yellow
    }
}
Write-Host "================================================" -ForegroundColor Cyan
Write-Host ""
