<?php
// Get the current URL without query parameters
$currentUrl = strtok($_SERVER["REQUEST_URI"], '?');

// Build the query string for the current parameters, excluding 'time'
$queryParams = array_diff_key($_GET, array('time' => ''));
$queryString = http_build_query($queryParams);
?>

<div class="dropdown">
  <button class="btn btn-sm fw-bold rounded-pill ms-2 my-2 btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
    <i class="bi bi-images"></i> Sort by
  </button>
  <ul class="dropdown-menu">
    <li><a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=day" class="dropdown-item <?php if(!isset($_GET['time']) || $_GET['time'] == 'day') echo 'fw-bold'; ?>">This Day</a></li>
    <li><a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=week" class="dropdown-item <?php if(isset($_GET['time']) && $_GET['time'] == 'week') echo 'fw-bold'; ?>">This Week</a></li>
    <li><a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=month" class="dropdown-item <?php if(isset($_GET['time']) && $_GET['time'] == 'month') echo 'fw-bold'; ?>">This Month</a></li>
    <li><a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=year" class="dropdown-item <?php if(isset($_GET['time']) && $_GET['time'] == 'year') echo 'fw-bold'; ?>">This Year</a></li>
    <li><a href="<?php echo $currentUrl; ?>?<?php echo $queryString; ?>&time=alltime" class="dropdown-item <?php if(isset($_GET['time']) && $_GET['time'] == 'alltime') echo 'fw-bold'; ?>">All Time</a></li>
  </ul>
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
<div class="w-100 px-1 mb-4">
  <a class="btn bg-body-secondary rounded-pill border-0 w-100 mt-3 fw-bold link-body-emphasis" href="/preview/rankings/?by=<?php if(!isset($_GET['time']) || $_GET['time'] == 'day') echo 'day'; ?>">View More</a>
</div>