<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }
?>

<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="bb1 container">
          <a class="nav-link" href="forum-chat/index.php"><i class="bi bi-chat-dots-fill"></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="favorite.php"><i class="bi bi-heart-fill"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle" style="font-size: 15.5px;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a></li>
              <li><a class="dropdown-item" href="tags.php"><i class="bi bi-tags"></i> Tags</a></li>
              <li><a class="dropdown-item" href="logout.php"><i class="bi bi-door-open-fill"></i> Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center> 
    <section class="gallery-links">
      <center>
        <div class="container">
          <h2 class="mt-5 mb-1" style="font-family: sans-serif; float: center; color: gray; font-weight: 800;">UPLOAD IMAGE</h2>
          <img id="file-ip-1-preview" style="height: 350px; border-radius: 8px; width: 100%; margin-bottom: 15px; margin-top: 20px;">
          <div class="gallery-upload">
            <form action="upload.php" method="post" enctype="multipart/form-data">
              <input class="form-control" type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);">
              <input class="form-control mt-3" type="text" name="tags" placeholder="Enter tags separated by comma">
              <input class="btn btn-primary fw-bold w-100 mt-3" type="submit" name="submit" value="upload">
            </form>
          </div>
        </div>
      </center>
    </section>
<style>
img {
    text-align: center;
    object-fit: cover;
    margin: auto;
    border: 4px solid #e6e5e3;
}
</style>
<script>
    const toggleSwitch = document.querySelector('#dark-mode-toggle');
    const body = document.querySelector('body');
    
    // Set initial state of toggle based on user preference
    if (localStorage.getItem('dark-mode') === 'enabled') {
      toggleSwitch.checked = true;
      body.classList.add('dark-mode');
    }
    
    // Listen for toggle change events
    toggleSwitch.addEventListener('change', () => {
      if (toggleSwitch.checked) {
        localStorage.setItem('dark-mode', 'enabled');
        body.classList.add('dark-mode');
      } else {
        localStorage.setItem('dark-mode', null);
        body.classList.remove('dark-mode');
      }
    });
</script>
<script>
          function showPreview(event){
  if(event.target.files.length > 0){
    var src = URL.createObjectURL(event.target.files[0]);
    var preview = document.getElementById("file-ip-1-preview");
    preview.src = src;
    preview.style.display = "block";
  }
}
    </script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
</body>
</html>
