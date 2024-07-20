<?php
// admin/analytic/data.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/admin/auth_admin.php');
requireAdmin();

// Open the database file
$db = new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite');

// Initialize variables
$data = array();

// Define date ranges
$current_date = new DateTime();
$this_week_start = (clone $current_date)->modify('this week');
$this_month_start = (clone $current_date)->modify('first day of this month');
$this_year_start = (clone $current_date)->modify('first day of January');

// Function to count files by date
function countFilesByDate($directory, $start_date = null) {
  $count = 0;
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
    if ($file->isFile()) {
      $file_date = new DateTime('@' . $file->getMTime());
      if (!$start_date || $file_date >= $start_date) {
        $count++;
      }
    }
  }
  return $count;
}

// Calculate image statistics
$image_dir = $_SERVER['DOCUMENT_ROOT'] . "/images";
$data['images']['this_week'] = countFilesByDate($image_dir, $this_week_start);
$data['images']['this_month'] = countFilesByDate($image_dir, $this_month_start);
$data['images']['this_year'] = countFilesByDate($image_dir, $this_year_start);
$data['images']['all_time'] = countFilesByDate($image_dir);

// Calculate video statistics
$videos_dir = $_SERVER['DOCUMENT_ROOT'] . "/feeds/minutes/videos";
$data['videos']['this_week'] = countFilesByDate($videos_dir, $this_week_start);
$data['videos']['this_month'] = countFilesByDate($videos_dir, $this_month_start);
$data['videos']['this_year'] = countFilesByDate($videos_dir, $this_year_start);
$data['videos']['all_time'] = countFilesByDate($videos_dir);

// Calculate music statistics
$music_dir = $_SERVER['DOCUMENT_ROOT'] . "/feeds/music/uploads";
$data['music']['this_week'] = countFilesByDate($music_dir, $this_week_start);
$data['music']['this_month'] = countFilesByDate($music_dir, $this_month_start);
$data['music']['this_year'] = countFilesByDate($music_dir, $this_year_start);
$data['music']['all_time'] = countFilesByDate($music_dir);

// Function to count rows by date in SQLite table
function countRowsByDate($db, $table, $date_column, $start_date = null) {
  $query = "SELECT COUNT(*) as count FROM $table";
  if ($start_date) {
    $query .= " WHERE DATE($date_column) >= DATE('" . $start_date->format('Y-m-d') . "')";
  }
  return $db->querySingle($query);
}

// Calculate user statistics
$data['users']['this_week'] = countRowsByDate($db, 'users', 'joined', $this_week_start);
$data['users']['this_month'] = countRowsByDate($db, 'users', 'joined', $this_month_start);
$data['users']['this_year'] = countRowsByDate($db, 'users', 'joined', $this_year_start);
$data['users']['all_time'] = countRowsByDate($db, 'users', 'joined');

// Calculate visit statistics
$data['visits']['this_week'] = countRowsByDate($db, 'visit', 'visit_date', $this_week_start);
$data['visits']['this_month'] = countRowsByDate($db, 'visit', 'visit_date', $this_month_start);
$data['visits']['this_year'] = countRowsByDate($db, 'visit', 'visit_date', $this_year_start);
$data['visits']['all_time'] = countRowsByDate($db, 'visit', 'visit_date');

// Calculate novel statistics
$data['novels']['this_week'] = countRowsByDate($db, 'novel', 'date', $this_week_start);
$data['novels']['this_month'] = countRowsByDate($db, 'novel', 'date', $this_month_start);
$data['novels']['this_year'] = countRowsByDate($db, 'novel', 'date', $this_year_start);
$data['novels']['all_time'] = countRowsByDate($db, 'novel', 'date');

// Count the number of users in the "users" table
$data['user_count'] = $db->querySingle('SELECT COUNT(*) FROM users');

// Count the total visit count from all rows in the "visit" table
$data['visit_count_total'] = $db->querySingle('SELECT SUM(visit_count) FROM visit');

// Count the total visit count from all rows in the "novel" table
$data['novel_count_total'] = $db->querySingle('SELECT SUM(id) FROM novel');

// Get visit count by date
$visit_by_date = $db->query('SELECT visit_date, SUM(visit_count) as total_visits FROM visit GROUP BY visit_date');
$data['visit_dates'] = [];
$data['visit_counts'] = [];
while ($row = $visit_by_date->fetchArray(SQLITE3_ASSOC)) {
  $data['visit_dates'][] = $row['visit_date'];
  $data['visit_counts'][] = $row['total_visits'];
}

// Get users joined by date
$users_joined_by_date = $db->query('SELECT joined, COUNT(*) as total_users FROM users GROUP BY joined');
$data['join_dates'] = [];
$data['join_counts'] = [];
while ($row = $users_joined_by_date->fetchArray(SQLITE3_ASSOC)) {
  $data['join_dates'][] = $row['joined'];
  $data['join_counts'][] = $row['total_users'];
}

// Count the number of images in the "images" table
$data['image_count'] = $db->querySingle('SELECT COUNT(*) FROM images');
$data['image_child_count'] = $db->querySingle('SELECT COUNT(*) FROM image_child');

// Count the number of videos in the "videos" table
$data['videos_count'] = $db->querySingle('SELECT COUNT(*) FROM videos');

// Count the number of music files in the directory
$data['music_count'] = countFilesByDate($music_dir);

// Sum the number of tags from the "images" table
$data['tag_count'] = $db->querySingle('SELECT SUM(LENGTH(tags) - LENGTH(REPLACE(tags, ",", "")) + 1) FROM images');

// Function to calculate the size of a directory
function calculateDirectorySize($directory) {
  $size = 0;
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
    if ($file->isFile()) {
      $size += $file->getSize();
    }
  }
  return $size;
}

// Calculate the total size of all images, music, and videos
$size = calculateDirectorySize($image_dir) + calculateDirectorySize($music_dir) + calculateDirectorySize($videos_dir);

// Convert the total size to MB
$data['total_size_data'] = number_format($size / 1048576, 2);

// NEW SECTION: Count uploads by date for images, music, and videos
function getUploadCountsByDate($db, $table, $date_column) {
  $query = "SELECT DATE($date_column) as upload_date, COUNT(*) as count 
            FROM $table 
            GROUP BY DATE($date_column) 
            ORDER BY upload_date DESC 
            LIMIT 30"; // Limit to last 30 days
  $result = $db->query($query);
  $counts = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $counts[$row['upload_date']] = $row['count'];
  }
  return $counts;
}

function getMusicUploadCountsByDate($directory) {
  $counts = [];
  $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
  foreach ($iterator as $file) {
    if ($file->isFile()) {
      $date = date('Y-m-d', $file->getMTime());
      if (!isset($counts[$date])) {
        $counts[$date] = 0;
      }
      $counts[$date]++;
    }
  }
  arsort($counts); // Sort in descending order by date
  return array_slice($counts, 0, 30); // Limit to last 30 days
}

$regions_query = $db->query('SELECT region, COUNT(*) as count FROM users GROUP BY region ORDER BY count DESC');
$data['regions'] = array();
while ($row = $regions_query->fetchArray(SQLITE3_ASSOC)) {
  $region = $row['region'] ? $row['region'] : 'Unknown';
  $data['regions'][$region] = $row['count'];
}

$data['uploads_by_date'] = [
  'images' => getUploadCountsByDate($db, 'images', 'date'),
  'music' => getMusicUploadCountsByDate($music_dir),
  'videos' => getUploadCountsByDate($db, 'videos', 'date')
];

// Close the database file
$db->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
?>