<?php
// fix_conflicts.php

$dir = __DIR__;
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$count = 0;

foreach ($files as $file) {
    if ($file->isDir())
        continue;

    // Skip this script itself
    if ($file->getRealPath() === __FILE__)
        continue;

    $content = file_get_contents($file->getRealPath());

    // Pattern to match merge conflicts and capture the incoming change
    // Matches: <<<<<<< HEAD ... ======= (captured) >>>>>>> hash
    // We want to keep the captured part.
    // Note: using [\s\S] to match newlines.

    $pattern = '/<<<<<<< HEAD[\r\n]+[\s\S]*?=======[\r\n]+([\s\S]*?)>>>>>>> [a-f0-9]+/';

    if (preg_match($pattern, $content)) {
        echo "Fixing conflicts in: " . $file->getPathname() . "\n";

        $newContent = preg_replace_callback($pattern, function ($matches) {
            return $matches[1];
        }, $content);

        // Also handle the potential trailing newline issue if the conflict is at the very end
        // Sometimes the markers might not have clean newlines if it's EOF.
        // But for now, let's stick to the standard format we saw.

        if ($newContent !== $content && $newContent !== null) {
            file_put_contents($file->getRealPath(), $newContent);
            $count++;
        }
    }
}

echo "Fixed conflicts in $count files.\n";
