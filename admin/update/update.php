<?php
// Configuration
$repo_owner = 'HirotakaDango';
$repo_name = 'ArtCODE';
$branch = 'main';

// Excluded files and directories (relative to document root)
$excluded = [
  'database.sqlite',
  'management.php',
  'admin/update/*'
];

// Special exceptions: files to be removed even if within excluded directories
$replaceExceptions = [
  'background_pictures/index.php',
  'background_pictures/forbidden.php',
  'images/index.php',
  'images/forbidden.php',
  'thumbnails/index.php',
  'thumbnails/forbidden.php',
  'profile_pictures/index.php',
  'profile_pictures/forbidden.php',
  'feeds/novel/images/index.php',
  'feeds/novel/thumbnails/index.php',
  'feeds/music/covers/index.php',
  'feeds/music/covers/default_cover.jpg',
  'feeds/music/covers/default_cover.png',
  'feeds/music/uploads/index.php',
  'feeds/minutes/videos/index.php',
  'feeds/minutes/thumbnails/index.php',
  'feeds/minutes/thumbnails/default_cover.jpg',
  'feeds/minutes/thumbnails/default_cover.png'
];

// Excluded directories with exceptions
$excludedDirectories = [
  'profile_pictures' => ['index.php', 'forbidden.php'],
  'background_pictures' => ['index.php', 'forbidden.php'],
  'images' => ['index.php', 'forbidden.php'],
  'thumbnails' => ['index.php', 'forbidden.php'],
  'feeds/novel/images' => ['index.php'],
  'feeds/novel/thumbnails' => ['index.php'],
  'feeds/music/covers' => ['index.php', 'default_cover.jpg', 'default_cover.png'],
  'feeds/music/uploads' => ['index.php'],
  'feeds/minutes/videos' => ['index.php'],
  'feeds/minutes/thumbnails' => ['index.php', 'default_cover.jpg', 'default_cover.png']
];

// Log file path
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/admin/update/log.json';

// Function to write log entry
function writeLog($message, $logFile) {
  $logEntries = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
  $logEntries[] = ['timestamp' => date('c'), 'message' => $message];
  file_put_contents($logFile, json_encode($logEntries, JSON_PRETTY_PRINT));
}

// Function to recursively copy files and directories
function copyDirectory($source, $destination, $excluded, $replaceExceptions, $excludedDirectories, $logFile, &$progress, $totalFiles, &$processedFiles) {
  @mkdir($destination, 0755, true);
  $files = array_diff(scandir($source), ['.', '..']);
  foreach ($files as $file) {
    $srcPath = $source . '/' . $file;
    $destPath = $destination . '/' . $file;
    $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'] . '/', '', $destPath);

    $excludedMatch = false;
    foreach ($excluded as $exclude) {
      $excludePattern = str_replace('*', '', $exclude);
      if (strpos($relativePath, $excludePattern) === 0) {
        $excludedMatch = true;
        break;
      }
    }

    $isReplaceException = in_array($relativePath, $replaceExceptions);
    $inExcludedDir = false;
    foreach ($excludedDirectories as $dir => $files) {
      if (strpos($relativePath, $dir . '/') === 0) {
        $inExcludedDir = true;
        break;
      }
    }

    if (!$excludedMatch || $isReplaceException) {
      if (is_dir($srcPath)) {
        copyDirectory($srcPath, $destPath, $excluded, $replaceExceptions, $excludedDirectories, $logFile, $progress, $totalFiles, $processedFiles);
      } else {
        if ($inExcludedDir && !$isReplaceException) {
          continue;
        }
        copy($srcPath, $destPath);
        $processedFiles++;
        $progress = ($processedFiles / $totalFiles) * 100;
        writeLog("Copied: $destPath", $logFile);
      }
    } elseif ($isReplaceException) {
      unlink($destPath);
      $processedFiles++;
      $progress = ($processedFiles / $totalFiles) * 100;
      writeLog("Removed excluded file: $destPath", $logFile);
    }
  }
}

// Function to count total files for progress
function countFiles($dir) {
  $totalFiles = 0;
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
  foreach ($iterator as $file) {
    if ($file->isFile()) {
      $totalFiles++;
    }
  }
  return $totalFiles;
}

// Function to remove directory
function rrmdir($dir, $logFile) {
  if (is_dir($dir)) {
    $objects = array_diff(scandir($dir), ['.', '..']);
    foreach ($objects as $object) {
      $path = $dir . "/" . $object;
      if (is_dir($path)) {
        rrmdir($path, $logFile);
      } else {
        unlink($path);
        writeLog("Deleted: $path", $logFile);
      }
    }
    rmdir($dir);
    writeLog("Removed directory: $dir", $logFile);
  }
}

$message = '';
$progress = 0;
$totalFiles = 0;
$processedFiles = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
  // Create a temporary directory
  $temp_dir = $_SERVER['DOCUMENT_ROOT'] . '/temp_update_' . time();
  mkdir($temp_dir);

  // Log the start of the update process
  writeLog("Started update process", $logFile);

  // Download the repository as a zip file
  $zip_url = "https://github.com/{$repo_owner}/{$repo_name}/archive/refs/heads/{$branch}.zip";
  $zip_file = $temp_dir . '/repo.zip';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $zip_url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Script');

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
    $message = 'Error downloading repository: ' . curl_error($ch);
    writeLog($message, $logFile);
  } else {
    file_put_contents($zip_file, $result);

    // Extract the zip file
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
      $zip->extractTo($temp_dir);
      $zip->close();
      writeLog("Extracted zip file", $logFile);

      // Get the extracted folder name
      $extracted_dir = glob($temp_dir . '/*', GLOB_ONLYDIR)[0];

      // Count total files for progress
      $totalFiles = countFiles($extracted_dir);

      // Move files to the current directory, excluding specified files/folders
      copyDirectory($extracted_dir, $_SERVER['DOCUMENT_ROOT'], $excluded, $replaceExceptions, $excludedDirectories, $logFile, $progress, $totalFiles, $processedFiles);

      // Clean up
      rrmdir($temp_dir, $logFile);
      writeLog("Update completed successfully", $logFile);

      $message = "Update completed successfully!";
      $progress = 100;
    } else {
      $message = 'Failed to extract the zip file';
      writeLog($message, $logFile);
    }
  }
  curl_close($ch);

  echo $message;
}
?>