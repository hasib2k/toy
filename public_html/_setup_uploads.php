<?php
/**
 * Upload Directories Setup Script
 * Run this once after deployment to create necessary upload directories
 * Access: https://yoursite.com/setup_uploads.php
 */

$directories = [
    'assets/images/uploads',
    'assets/videos/uploads'
];

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Upload Directories</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; padding: 10px; background: #d4edda; border-radius: 4px; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border-radius: 4px; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border-radius: 4px; margin: 10px 0; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <h1>Upload Directories Setup</h1>
    <p>This script will create necessary upload directories for your website.</p>
    <hr>
";

foreach ($directories as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    
    echo "<h3>Creating: $dir</h3>";
    
    if (file_exists($fullPath)) {
        echo "<div class='info'>Directory already exists: $fullPath</div>";
        
        // Check if writable
        if (is_writable($fullPath)) {
            echo "<div class='success'>✓ Directory is writable</div>";
        } else {
            echo "<div class='error'>✗ Directory is NOT writable. Please set permissions to 755 or 777</div>";
        }
    } else {
        // Try to create directory
        if (mkdir($fullPath, 0755, true)) {
            echo "<div class='success'>✓ Directory created successfully: $fullPath</div>";
            
            // Verify it's writable
            if (is_writable($fullPath)) {
                echo "<div class='success'>✓ Directory is writable</div>";
            } else {
                echo "<div class='error'>⚠ Directory created but may not be writable. Please check permissions.</div>";
            }
        } else {
            echo "<div class='error'>✗ Failed to create directory: $fullPath</div>";
            echo "<div class='error'>Please create this directory manually and set permissions to 755 or 777</div>";
        }
    }
    
    echo "<hr>";
}

echo "
    <h2>Summary</h2>
    <p>All upload directories have been processed. If you see any errors above, please:</p>
    <ol>
        <li>Create the directories manually via FTP/File Manager</li>
        <li>Set permissions to 755 or 777</li>
        <li>Refresh this page to verify</li>
    </ol>
    
    <h2>Security Note</h2>
    <p style='background: #fff3cd; padding: 15px; border-radius: 4px;'>
        <strong>⚠ Important:</strong> After setup is complete, delete this file (<code>setup_uploads.php</code>) for security reasons.
    </p>
    
    <p><a href='admin/content.php'>Go to Admin Content Management →</a></p>
</body>
</html>
";
