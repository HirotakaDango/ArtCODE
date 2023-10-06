<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="../manifest.json">
    <link rel="icon" type="image/png" href="../icon/favicon.png">
    <link rel="stylesheet" type="text/css" href="style.css" />
    <?php include('../bootstrapcss.php'); ?>
  </head>
  <body>
    <main class="page-wrapper w-100">
      <!-- Hero -->
      <div class="bg-dark pb-5" style="background-image: url('../session/contents/mountain-1.jpg'); background-size: cover; background-repeat: no-repeat; height: 100vh;">
        <!-- Navbar -->
        <?php include('lp_header.php');?>
        <!-- End of Navbar -->
        
        <br>
        
        <!-- Main -->
        <?php include('main.php');?>
        <!-- End of Main -->
        
        <div style="padding-bottom: 200px;"></div>
      </div>
      
      <!-- Features -->
      <?php include('features.php');?>
      <!-- End of Features -->
      
      <!-- Advantages -->
      <?php include('advantages.php');?>
      <!-- End of Advantages -->
      
    </main>

    <!-- Footer -->
    <?php include('footer.php');?>
    <!-- End of footer -->
    
    <!-- additional style -->
    <style>
      .feature-icon {
        width: 4rem;
        height: 4rem;
        border-radius: .75rem;
      }

      .icon-square {
        width: 3rem;
        height: 3rem;
        border-radius: .75rem;
      }

      .text-shadow-1 { text-shadow: 0 .125rem .25rem rgba(0, 0, 0, .25); }
      .text-shadow-2 { text-shadow: 0 .25rem .5rem rgba(0, 0, 0, .25); }
      .text-shadow-3 { text-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .25); }

      .card-cover {
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
      }

      .feature-icon-small {
        width: 3rem;
        height: 3rem;
      }
    </style>
    <!-- additional style -->
    <?php include('../bootstrapjs.php'); ?>
  </body>
</html>