<?php
  session_start();
  if (!isset($_SESSION['username'])) {
    header("Location: session.php");
    exit;
  }
?>

<html lang="en">
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ArtCODE</title>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="icon/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">
  </head>
  <body>
    <center style="margin-bottom: 60px; font-weight: 800; color: gray;">
      <nav class="navbar fixed-top bg-light shadow" style="padding-bottom: 7px; padding-top: 7px;">
        <div class="bb1 container">
          <a class="nav-link px-2 text-secondary" href="index.php"><i class="bi bi-house-fill"></i></i></a>
          <a class="nav-link px-2 text-secondary" href="imgupload.php"><i class="bi bi-cloud-arrow-up-fill"></i></i></a>
          <h1 style="color: gray; margin-top: 7px;" class="nav-link px-2 text-secondary"><a class="nav-link border-bottom" href="index.php">ArtCODE LITE</a></h1>
          <a class="nav-link px-2 text-secondary" href="profile.php"><i class="bi bi-person-circle"></i></a>
          <div class="dropdown">
            <a class="nav-link px-2 text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-door-open-fill"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start">
              <li><a class="dropdown-item" href="session.php">Signin</a></li>
              <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
          </div>
        </div>
      </nav>
    </center>
    <section class="gallery-links">
        <div class="wrapper">
            <h2 style="font-family: sans-serif; float: center; color: gray; font-weight: 800;">UPLOAD IMAGE</h2>

            <img id="file-ip-1-preview" style="height: 400px; border-radius: 15px; width: 95%; margin-bottom: 15px; margin-top: 20px;">
            <div class="gallery-upload">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                       
                  <?php if (isset($_GET['error'])): ?>
                        <p><?php echo $_GET['error']; ?></p>
                  <?php endif ?>
                    </br>
                    <div class="upload-btn-wrapper">
                        <label for="file-ip-1" hidden>upload</label>
                        <button class="btn1">browse</button>
                        <input type="file" name="image" type="file" id="file-ip-1" accept="image/*" onchange="showPreview(event);">
                    </div>
                    <div class="upload-btn-wrapper">
                        <input type="submit" 
                               name="submit" 
                               value="upload" 
                               class="btn1">  
                    </div>
                </form>
            </div>
        </div>
    </section>
<style>
input[type=text] {
  padding:10px;
  border: 2px solid #eee;
  width: 90%;
  margin: auto;
  margin-bottom: 10px;
  border-radius: 15px;
}

.btn1 {
  padding: 10px;
  margin: 10px; 
  border: 8px solid #eee;
  border-radius: 15px;
  color: gray;
  font-weight: 700;
  padding: 8px 20px;
}

.gallery-links {
    text-align: center;
}

.gallery-upload {
    text-align: center;
}

img {
    text-align: center;
    object-fit: cover;
    margin: auto;
    border: 4px solid #e6e5e3;
}

.center {
    text-align: center;
}

.btn {
    border: 2px solid #eee;
    color: gray;
    background-color: #eee;
    padding: 8px 20px;
    border-radius: 15px;
    font-size: 20px;
    font-weight: bold;
}

.upload-btn-wrapper input[type=file] {
    font-size: 100px;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
}

.upload-btn-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
}

</style>

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
