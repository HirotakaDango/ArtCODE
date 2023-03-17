<?php
// Connect to the SQLite database
$db = new SQLite3('database.sqlite');

// Retrieve the total number of news items from the database
$count_results = $db->query('SELECT COUNT(*) as count FROM news');
$count_row = $count_results->fetchArray(SQLITE3_ASSOC);
$count = $count_row['count'];

// Calculate the total number of pages
$pages = ceil($count / 20);

// Get the current page number or default to 1
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Calculate the starting offset for the current page
$offset = ($page - 1) * 20;

// Retrieve the news items for the current page from the database
$results = $db->query("SELECT * FROM news ORDER BY id DESC LIMIT $offset, 20");
$data = [];

// Loop through each row in the result set
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
  $data[] = $row;
}

// Close the database connection
$db->close();

// Calculate previous and next page numbers
$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $pages ? $page + 1 : $pages;

?>

<!DOCTYPE html>
<html>
  <head>
    <title>My Table</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <header>
      <div class="d-flex flex-column flex-md-row align-items-center text-secondary fw-bold pb-3 mb-4 container">
        <h1 href="/" class="d-flex align-items-center text-dark text-decoration-none">
          <div class="row">
            <div class="col-md-12 text-center py-2">
              <h1 class="animate__animated animate__fadeInDown"><a class="text-decoration-none text-secondary fw-bold" href="index.php">ArtCODE</a></h1>
              <h4 class="animate__animated animate__fadeInUp text-secondary fw-bold">Inspiring Art Collection</h4>
            </div>
          </div>    
        </h1>
        <nav class="d-inline-flex mt-2 mt-md-0 ms-md-auto">
          <a class="me-3 py-2 text-dark text-decoration-none" href="session.php">Features</a>
          <a class="me-3 py-2 text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#signin">Signin</a>
          <a class="me-3 py-2 text-dark text-decoration-none" data-bs-toggle="modal" data-bs-target="#signup">Signup</a>
          <a class="py-2 text-dark text-decoration-none" href="newspage.php">News</a>
        </nav>
      </div>
    </header>
    <h2 class="text-secondary mt-2 fw-bold text-center"><i class="bi bi-newspaper"></i> Update & News</h2>
    <div class="container">
      <div class="row">
        <div class="container">
          <?php foreach ($data as $row): ?>  
            <div class="card mb-3">
              <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-newspaper"></i>
                News <p class="btn-sm text-white float-end"></p>
              </div>
              <div class="card-body text-secondary fw-bold">
                <p class="text-start ms-3">Title: <?= $row['title'] ?></p>
                <p class="text-start ms-3">Description: <?= $row['description'] ?></p>
                <a class="text-start text-primary text-decoration-none ms-3" href="<?= $row['verlink'] ?>" target="_blank"><?= $row['ver'] ?></a>
                <p class="text-start ms-3">Created At: <?= $row['created_at'] ?></p>
              </div>
            </div>
          <?php endforeach; ?> 
        </div>
      </div>
    </div>
    <div class="mt-3 d-flex justify-content-center btn-toolbar container"> 
      <?php if ($page > 1): ?>
        <a class="btn btn-sm rounded-pill fw-bold btn-secondary opacity-50 me-1" href="?page=<?php echo $prevPage; ?>"><i class="bi bi-arrow-left-circle-fill"></i> prev</a>
      <?php endif; ?>
      <?php if ($page < $pages): ?>
        <a class="btn btn-sm rounded-pill fw-bold btn-secondary opacity-50 ms-1" href="?page=<?php echo $nextPage; ?>">next <i class="bi bi-arrow-right-circle-fill"></i></a>
      <?php endif; ?>
    </div>
    <div class="mt-5"></div>
    <div class="modal fade" id="signin" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Sign In</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <center><h1><i class="bi bi-person-circle"></i></h1></center>
            <center><h2 class="fw-bold">WELCOME BACK!</h2></center>
            <center><h2 class="mb-5 fw-bold">LOGIN TO CONTINUE</h2></center>
            <div class="modal-body p-4 pt-0">
              <form class="" action="session_code.php" method="post">
                <div class="form-floating mb-3">
                  <input name="username" type="email" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                  <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                  <label for="floatingPassword">Password</label>
                </div>
                <button name="login" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Login</button>
                <p>Don't have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signup" data-bs-toggle="modal">Signup</button></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="signup" aria-hidden="true" aria-labelledby="exampleModalToggleLabel2" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalToggleLabel2">Sign Up</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <center><h1><i class="bi bi-person-circle"></i></h1></center>
            <center><h2 class="fw-bold">HELLO, NEW USER?</h2></center>
            <center><h2 class="mb-5 fw-bold">REGISTER TO CONTINUE</h2></center>
            <div class="modal-body p-4 pt-0">
              <form class="" action="session_code.php" method="post">
                <div class="form-floating mb-3">
                  <input name="artist" type="text" class="form-control rounded-3" maxlength="40" id="floatingInput" placeholder="Your name" required>
                  <label for="floatingInput">Your name</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="username" type="email" class="form-control rounded-3" id="floatingInput" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="name@example.com" required>
                  <label for="floatingInput">Email address</label>
                </div>
                <div class="form-floating mb-3">
                  <input name="password" type="password" class="form-control rounded-3" id="floatingPassword" maxlength="40" pattern="^[a-zA-Z0-9_@.-]+$" placeholder="Password" required>
                  <label for="floatingPassword">Password</label>
                </div>
                <button name="register" class="w-100 fw-bold mb-2 btn btn-lg rounded-3 btn-primary" type="submit">Register</button>
                <p>Already have an account? <button class="text-decoration-none btn btn-primary btn-sm text-white fw-bold rounded-pill opacity-75" data-bs-target="#signin" data-bs-toggle="modal">Signin</button></p>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
