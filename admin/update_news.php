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
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'created_at' => date('Y-m-d H:i:s'), 
        'ver' => $_POST['ver'],
        'verlink' => $_POST['verlink']
      ];
      if (isset($_POST['id'])) {
        $data['id'] = $_POST['id'];
      }
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

ob_start(); // start output buffering

$userController = new UserController('../database.sqlite', 'news');
$userController->addData();
$userController->updateData();
$userController->deleteData();
$data = $userController->getAllData();
$edit_data = $userController->getEditData();

ob_end_flush(); // end output buffering and send output to the browser

?>

    <?php include('admin_header.php'); ?>
    <center><button type="button" class="btn btn-primary fw-bold mt-2 mb-2" data-bs-toggle="modal" data-bs-target="#add">Add New News</button></center>
    <center>
      <div class="container-fluid">
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
                  <textarea style="height: 200px;" type="text" placeholder="description" name="description" value="<?= $edit_data['description'] ?>" class="form-control" required><?= $edit_data['description'] ?></textarea>
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="version" name="ver" value="<?= $edit_data['ver'] ?>" class="form-control">
                </div>

                <div class="mb-3">
                  <input type="text" placeholder="link" name="verlink" value="<?= $edit_data['verlink'] ?>" class="form-control">
                </div>

                <button type="submit" name="update" class="btn btn-primary mb-2 fw-bold">Update</button>
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
                  <textarea style="height: 200px;" type="text" name="description" class="form-control" placeholder="description" required></textarea>
                </div>
                <div class="mb-3">
                  <input type="text" name="ver" class="form-control" placeholder="version">
                </div> 
                <div class="mb-3">
                  <input type="text" name="verlink" class="form-control" placeholder="link">
                </div>
                <button type="submit" name="add" class="btn btn-primary fw-bold">Add</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="mb-3"></div>
    <?php include('end.php'); ?>