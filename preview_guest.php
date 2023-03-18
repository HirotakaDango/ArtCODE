<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <script src="script.js"></script>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <?php include('lp_header.php'); ?>
    <div class="dropdown ms-3 mb-2">
      <button class="form-control text-secondary fw-bold width-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        Sort by
      </button>
      <ul class="dropdown-menu">
        <li><a class="dropdown-item fw-bold <?php if(basename($_SERVER['PHP_SELF']) == '?by=newest') echo 'active' ?>" href="?by=newest">Newest</a></li>
        <li><a class="dropdown-item fw-bold <?php if(basename($_SERVER['PHP_SELF']) == '?by=oldest') echo 'active' ?>" href="?by=oldest">Oldest</a></li>
      </ul>
    </div>
        <?php 
        if(isset($_GET['by'])){
            $sort = $_GET['by'];
 
            switch ($sort) {
                case 'newest':
                include "preview_guest_desc.php";
                break;
                case 'oldest':
                include "preview_guest_asc.php";
                break;
            }
        }
        else{
            include "preview_guest_desc.php";
        }
        
        ?>
    <style>
      @media (min-width: 768px) {
        .width-btn {
          width: 200px;
        }
      }
      
      @media (max-width: 767px) {
        .width-btn {
          width: 100px;
        } 
      } 
    </style>
  </body>
</html>