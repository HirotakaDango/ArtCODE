<?php
// Get the current URL without query parameters
$currentUrl = strtok($_SERVER["REQUEST_URI"], '?');

// Build the query string for the current parameters, excluding 'time'
$queryParams = array_diff_key($_GET, array('time' => ''));
$queryString = http_build_query($queryParams);
?>

<div class="container-fluid">
  <div class="position-absolute top-0 end-0 z-2" style="margin-top: 8em; margin-right: 7em;">
    <div class="btn-group ms-auto mb-2">
      <a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=day" class="btn btn-sm me-2 text-white border-0 <?php if(!isset($_GET['time']) || $_GET['time'] == 'day') echo 'fw-bold'; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">this day</a>
      <a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=week" class="btn btn-sm me-2 text-white border-0 <?php if(isset($_GET['time']) && $_GET['time'] == 'week') echo 'fw-bold'; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">this week</a>
      <a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=month" class="btn btn-sm me-2 text-white border-0 <?php if(isset($_GET['time']) && $_GET['time'] == 'month') echo 'fw-bold'; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">this month</a>
      <a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=year" class="btn btn-sm me-2 text-white border-0 <?php if(isset($_GET['time']) && $_GET['time'] == 'year') echo 'fw-bold'; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">this year</a>
      <a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=alltime" class="btn btn-sm text-white border-0 <?php if(isset($_GET['time']) && $_GET['time'] == 'alltime') echo 'fw-bold'; ?>" style="text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.4), 2px 2px 4px rgba(0, 0, 0, 0.3), 3px 3px 6px rgba(0, 0, 0, 0.2);">all time</a>
    </div>
    <div class="border border-light shadow"></div>
  </div>
</div>

<?php 
if(isset($_GET['time'])){
  $sort = $_GET['time'];

  switch ($sort) {
    case 'day':
      include "daily.php";
      break;
    case 'week':
      include "week.php";
      break;
    case 'month':
      include "month.php";
      break;
    case 'year':
      include "year.php";
      break;
    case 'alltime':
      include "alltime.php";
      break;
  }
}
else {
  include "daily.php";
}
?>