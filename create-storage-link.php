<?php

$target = __DIR__.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public';
$link = __DIR__.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'storage';

if (file_exists($link)) {
    echo "Storage link already exists.\n";
    exit(0);
}

if (is_dir($target)) {
    if (@symlink($target, $link)) {
        echo "Storage link created successfully.\n";
    } else {
        echo "Failed to create symlink, trying directory junction...\n";
        exec("mklink /J \"{$link}\" \"{$target}\"", $output, $result);
        if ($result === 0) {
            echo "Storage junction created successfully.\n";
        } else {
            echo "Failed to create storage link. Please run: php artisan storage:link\n";
        }
    }
} else {
    echo "Target directory does not exist: {$target}\n";
}
