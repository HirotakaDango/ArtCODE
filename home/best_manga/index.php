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