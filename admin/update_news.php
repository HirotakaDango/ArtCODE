<?php

require_once('datatable.php');
require_once('prompt.php'); 

class UserController {
  private $datatable;

  public function __construct($db_file, $table_name) {
    $this->datatable = new DataTable($db_file, $table_name);
  }

  public function addData() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
      $data = [
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'created_at' => date('Y-m-d H:i:s'), 
        'ver' => $_POST['ver'],
        'verlink' => $_POST['verlink']
      ];
      $this->datatable->insert_data($data);
      header("Location: ../admin/update_news.php");
      exit();
    }
  }
 
  public function updateData() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
      $data = [
        'id' => $_POST['id'],
        'title' => $_POST['title'],
        'description' => $_POST['description'], 
        'ver' => $_POST['ver'],
        'verlink' => $_POST['verlink']
      ];
      $this->datatable->update_data($data);
      header("Location: ../admin/update_news.php");
      exit();
    }
  }

public function deleteData() {
  if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $this->datatable->delete_data($id);
    header("Location: ../admin/update_news.php");
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

$userController = new UserController('../database.sqlite', 'news');
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
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  </head>
  <body>
    <ul class="me-2 ms-2 mt-2 nav nav-pills nav-fill justify-content-center">
      <li class="nav-item"><a class="nav-link" href="../admin/index.php"><i class="bi bi-house-fill"></i></a></li>
      <li class="nav-item"><a class="nav-link" href="../admin/edit_users.php"><i class="bi bi-person-fill-gear"></i></a></li>
      <li class="nav-item"><a class="nav-link active" aria-current="page" href="../admin/update_news.php"><i class="bi bi-newspaper"></i></a></li>
      <li class="nav-item"><a class="nav-link" href="../admin/remove_images.php"><i class="bi bi-images"></i></a></li> 
      <li class="nav-item"><a class="nav-link" href="../admin/remove_all.php"><i class="bi bi-person-fill-exclamation"></i></a></li>
    </ul>
    <center><button type="button" class="btn btn-primary fw-bold mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#add">Add New News</button></center>
    <center>
      <div class="container-fluid mb-2">
        <div class="card-container">
          <?php if ($edit_data !== null): ?>
            <div class="card text-secondary fw-bold container">
              <h5 class="text-center text-secondary fw-bold mt-2">Edit News</h5> 
              <form method="post" enctype="multipart/form-data">
        
                <input placeholder="" type="hidden" name="id" value="<?= $edit_data['id'] ?>">
          
                <div class="mb-3">
                  <input type="text" placeholder="title" name="title" value="<?= $edit_data['title'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                  <textarea row="5" type="text" placeholder="description" name="description" value="<?= $edit_data['description'] ?>" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="version" name="ver" value="<?= $edit_data['ver'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="link" name="verlink" value="<?= $edit_data['verlink'] ?>" class="form-control" required>
                </div>

                <button type="submit" name="update" class="btn btn-primary mb-2">Update</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div> 
    </center>
    <div class="container">
      <div class="row">
        <div class="container">
          <?php foreach ($data as $row): ?>  
            <div class="card mb-3">
              <div class="card-header bg-primary text-white fw-bold">
                <i class="bi bi-newspaper"></i>
                News <a href="?edit=<?= $row['id'] ?>" class="btn-sm text-white float-end"><i class="bi bi-pencil-fill"></i></a>
              </div>
              <div class="card-body text-secondary fw-bold">
                <p class="text-start ms-3">Title: <?= $row['title'] ?></p>
                <p class="text-start ms-3">Desc: <?= $row['description'] ?></p>
                <p class="text-start ms-3">Date: <?= $row['created_at'] ?></p>
                <p class="text-start ms-3">Version: <?= $row['ver'] ?></p>
                <p class="text-start ms-3">Link: <a class="text-primary text-decoration-none" href="<?= $row['verlink'] ?>" target="_blank"><?= $row['verlink'] ?></a></p>
                <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="bi bi-trash"></i></a>
              </div>
            </div>
          <?php endforeach; ?> 
        </div>
      </div>
    </div>
    <div>
      <div class="modal fade" id="add" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header text-secondary">
              <h4>Add News</h4>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                  <input type="text" name="title" class="form-control" placeholder="title" required>
                </div>
                <div class="mb-3">
                  <textarea type="text" name="description" class="form-control" placeholder="description" required></textarea>
                </div>
                <div class="mb-3">
                  <input type="text" name="ver" class="form-control" placeholder="version" required>
                </div> 
                <div class="mb-3">
                  <input type="text" name="verlink" class="form-control" placeholder="link" required>
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
