<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../admin/access.php');
    exit;
}

require_once('datatable.php');

class UserController {
  private $datatable;

  public function __construct($db_file, $table_name) {
    $this->datatable = new DataTable($db_file, $table_name);
  }

  public function addData() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
      $data = [
        'id' => $_POST['id'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'artist' => $_POST['artist'],
        'desc' => $_POST['desc']
      ];
      $this->datatable->insert_data($data);
      header("Location: ../admin/index.php");
      exit();
    }
  }

  public function updateData() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
      $data = [
        'id' => $_POST['id'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'artist' => $_POST['artist'],
        'desc' => $_POST['desc']
      ];
      $this->datatable->update_data($data);
      header("Location: ../admin/index.php");
      exit();
    }
  }

  public function deleteData() {
    if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      $this->datatable->delete_data($id);
      header("Location: ../admin/index.php");
      exit();
    }
  }

  public function getAllData() {
    return $this->datatable->get_all_data();
  }

  public function getEditData() {
    $edit_data = null;
    if (isset($_GET['edit'])) {
      $id = $_GET['edit'];
      $edit_data = $this->datatable->get_data_by_id($id);
    }
    return $edit_data;
  }
}

$userController = new UserController('../database.sqlite', 'users');
$userController->addData();
$userController->updateData();
$userController->deleteData();
$data = $userController->getAllData();
$edit_data = $userController->getEditData();
?>

<!DOCTYPE html>
<html>
  <head>
    <title>My Table</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <style>
      .card-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        grid-gap: 20px;
      }

      .card {
        border: 1px solid #ccc;
        border-radius: 5px;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);
        padding: 10px;
        width: 100%;
        height: auto;
      }

      .card p {
        font-size: 1.2em;
        margin: 0;
        padding: 0;
        margin: 10px 0 0 0;
        padding: 0;
      }

      .price {
        font-weight: bold;
        color: #009900;
        font-size: 1.2em;
      }
      
     .click {
        width: 40%;
     }
    </style>
  </head>
  <body>
    <center>
      <div class="container-fluid mt-2">
        <div class="card-container">
          <?php if ($edit_data !== null): ?>
            <div class="card text-secondary fw-bold">
              <h1 class="text-center text-secondary fw-bold">Edit User's Data</h1> 
              <form method="post" enctype="multipart/form-data">
        
                <input placeholder="" type="hidden" name="id" value="<?= $edit_data['id'] ?>">
          
                <div class="mb-3">
                  <input type="text" placeholder="email" name="username" value="<?= $edit_data['username'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="password" name="password" value="<?= $edit_data['password'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="name" name="artist" value="<?= $edit_data['artist'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="bio" name="desc" value="<?= $edit_data['desc'] ?>" class="form-control" required>
                </div> 

                <button type="submit" name="update" class="btn btn-primary">Update</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div> 
    </center>
    <center>
      <div class="container-fluid mt-2">
        <h1 class="text-center text-secondary fw-bold">Users <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add"><i class="bi bi-person-fill-add"></i></button></h1>
        <div class="card-container">
          <?php foreach ($data as $row): ?>
            <div class="card text-secondary fw-bold">
              <p class="text-start ms-3">User ID: <?= $row['id'] ?></p>
              <p class="text-start ms-3">Email: <?= $row['username'] ?></p>
              <p class="text-start ms-3">Password: <?= $row['password'] ?></p>
              <p class="text-start ms-3">Name: <?= $row['artist'] ?></p>
              <p class="text-start ms-3">Bio: <?= $row['desc'] ?></p>
              <p><a href="?edit=<?= $row['id'] ?>" class="btn btn-warning click"><i class="bi bi-pencil-fill"></i></a> <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger click" onclick="return confirm('Are you sure?')"><i class="bi bi-trash-fill"></i></a></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div> 
    </center>
    <div>
      <div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header text-secondary">
              <h4>Add User</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="email" required>
              </div>

              <div class="mb-3">
                <input type="text" name="password" class="form-control" placeholder="password" required>
              </div>

              <div class="mb-3">
                <input type="text" name="artist" class="form-control" placeholder="name" required>
              </div>

              <div class="mb-3">
                <input type="text" name="desc" class="form-control" placeholder="bio" required>
              </div> 

              <button type="submit" name="add" class="btn btn-success">Add</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-3"></div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
