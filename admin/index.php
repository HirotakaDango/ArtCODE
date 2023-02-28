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
        'pic' => $_POST['telp'], 
        'desc' => $_POST['desc'], 
        'bgpic' => $_POST['bgpic']
      ];
      $this->datatable->insert_data($data);
      header("Location: index.php");
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
        'pic' => $_POST['telp'], 
        'desc' => $_POST['desc'], 
        'bgpic' => $_POST['bgpic']
      ];
      $this->datatable->update_data($data);
      header("Location: index.php");
      exit();
    }
  }

  public function deleteData() {
    if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      $this->datatable->delete_data($id);
      header("Location: index.php");
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
  </head>
  <body>
    <center class="mb-3">
      <h1 class="mb-3">My Table</h1>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add">Tambah Data</button>
    </center> 
    <div class="containee-fluid table-responsive">
      <table class="table table-bordered text-center">
        <thead>
          <tr>
            <th>ID</th>
            <th>USER</th>
            <th>PASSWORD</th>
            <th>ARTIST NAME</th>
            <th>USER'S INFORMATION</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data as $row): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['username'] ?></td>
              <td><?= $row['password'] ?></td>
              <td><?= $row['artist'] ?></td>
              <td><p><?= $row['desc'] ?></p></td>
              <td>
                <a href="?edit=<?= $row['id'] ?>" class="btn btn-warning"><i class="bi bi-pencil"></i></a>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h4>Tambah Data</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="post" enctype="multipart/form-data">
              <div class="mb-3">
                <label for="username" class="form-label">USERNAME:</label>
                <input type="text" name="username" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">PASSWORD:</label>
                <input type="text" name="password" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="artist" class="form-label">ARTIST NAME:</label>
                <input type="text" name="artist" class="form-control" required>
              </div>

              <div class="mb-3">
                <label for="desc" class="form-label">USER'S INFORMATION:</label>
                <input type="text" name="desc" class="form-control" required>
              </div> 

              <button type="submit" name="add" class="btn btn-success">Add</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <?php if ($edit_data !== null): ?>
        <h2>Edit Data</h2>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $edit_data['id'] ?>">

          <div class="mb-3">
            <label for="username" class="form-label">USERNAME:</label>
            <input type="text" name="username" value="<?= $edit_data['username'] ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">PASSWORD:</label>
            <input type="text" name="password" value="<?= $edit_data['password'] ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="artist" class="form-label">ARTIST NAME:</label>
            <input type="text" name="artist" value="<?= $edit_data['artist'] ?>" class="form-control" required>
          </div>

          <div class="mb-3">
            <label for="desc" class="form-label">USER'S INFORMATION:</label>
            <input type="text" name="desc" value="<?= $edit_data['desc'] ?>" class="form-control" required>
          </div> 

          <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
      <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-mQ93GR66B00ZXjt0YO5KlohRA5SY2XofN4zfuZxLkoj1gXtW8ANNCe9d5Y3eG5eD" crossorigin="anonymous"></script>
  </body>
</html>
