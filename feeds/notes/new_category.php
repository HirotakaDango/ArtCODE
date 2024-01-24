<?php
require_once('auth.php');

$email = $_SESSION['email'];

$db = new PDO('sqlite:../../database.sqlite');

// Check if the delete button is pressed
if (isset($_POST['delete_category'])) {
  // Sanitize and filter user inputs
  $category_name_to_delete = filter_input(INPUT_POST, 'category_name_to_delete', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Prepare and execute the SQL query to delete the category
  $stmtDeleteCategory = $db->prepare("DELETE FROM category WHERE category_name = :category_name AND email = :email");
  $stmtDeleteCategory->bindParam(':category_name', $category_name_to_delete);
  $stmtDeleteCategory->bindParam(':email', $email);
  $stmtDeleteCategory->execute();

  // Redirect to the same page to refresh the category list
  header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/new_category.php');
  exit(); // Add this line to stop script execution after redirect
}

// Check if the form is submitted to update a category
if (isset($_POST['update_category'])) {
  // Sanitize and filter user inputs
  $updated_category_name = filter_input(INPUT_POST, 'update_category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
  $category_id_to_update = filter_input(INPUT_POST, 'category_id_to_update', FILTER_VALIDATE_INT);

  if ($category_id_to_update !== false) {
    // Check if the updated category name already exists for the current logged-in email
    $existingCategoryQuery = "SELECT COUNT(*) FROM category WHERE category_name = :updated_category_name AND email = :email AND id != :category_id";
    $stmtExistingCategory = $db->prepare($existingCategoryQuery);
    $stmtExistingCategory->bindParam(':updated_category_name', $updated_category_name);
    $stmtExistingCategory->bindParam(':email', $email);
    $stmtExistingCategory->bindParam(':category_id', $category_id_to_update, PDO::PARAM_INT);
    $stmtExistingCategory->execute();
    $existingCategoryCount = $stmtExistingCategory->fetchColumn();

    if ($existingCategoryCount > 0) {
      // Category with the updated name already exists, show alert
      echo '<script>';
      echo 'alert("Can\'t change this category because you already have a category with the same name.");';
      echo 'window.location.href = "' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/new_category.php";';
      echo '</script>';
      exit(); // Stop script execution after displaying the alert and redirecting
    }

    // Prepare and execute the SQL query to update the category name
    $stmtUpdateCategory = $db->prepare("UPDATE category SET category_name = :updated_category_name WHERE id = :category_id AND email = :email");
    $stmtUpdateCategory->bindParam(':updated_category_name', $updated_category_name);
    $stmtUpdateCategory->bindParam(':category_id', $category_id_to_update, PDO::PARAM_INT);
    $stmtUpdateCategory->bindParam(':email', $email);
    $stmtUpdateCategory->execute();

    // Redirect to the same page to refresh the category list
    header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/new_category.php');
    exit(); // Add this line to stop script execution after redirect
  }
}

// Check if the form is submitted to add a new category
if (isset($_POST['submit'])) {
  // Sanitize and filter user inputs
  $category_name = filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);

  // Check if the category already exists for the current logged-in email
  $existingCategoryQuery = "SELECT COUNT(*) FROM category WHERE category_name = :category_name AND email = :email";
  $stmtExistingCategory = $db->prepare($existingCategoryQuery);
  $stmtExistingCategory->bindParam(':category_name', $category_name);
  $stmtExistingCategory->bindParam(':email', $email);
  $stmtExistingCategory->execute();
  $existingCategoryCount = $stmtExistingCategory->fetchColumn();

  if ($existingCategoryCount > 0) {
    // Category already exists for the current logged-in email, show alert
    echo '<script>';
    echo 'alert("This category already exists.");';
    echo 'window.location.href = "' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/new_category.php";';
    echo '</script>';
    exit(); // Stop script execution after displaying the alert and redirecting
  } else {
    // Prepare and execute the SQL query to insert the new category
    $stmtInsertCategory = $db->prepare("INSERT INTO category (category_name, email) VALUES (:category_name, :email)");
    $stmtInsertCategory->execute(array(':category_name' => $category_name, ':email' => $email));

    // Redirect to the same page to refresh the category list
    header('Location: ' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/feeds/notes/new_category.php');
    exit(); // Add this line to stop script execution after redirect
  }
}

// Query to get distinct categories and count of posts for each category based on email
$category_query = "
  SELECT category_name, id AS category_id
  FROM category
  WHERE email = :email
  ORDER BY category_name ASC
";
$stmt = $db->prepare($category_query);
$stmt->bindParam(':email', $email);
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
  <head>
    <link rel="icon" type="image/png" href="../../icon/favicon.png">
    <title>New Categories</title>
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php include('../../bootstrapcss.php'); ?>
  </head>
  <body>
    <?php include('header.php'); ?>
    <form method="post" enctype="multipart/form-data" class="container mt-3">
      <div class="form-floating mb-2">
        <input class="form-control rounded-4 bg-body-tertiary border-0 focus-ring focus-ring-dark" type="text" name="category_name" placeholder="Add new category" maxlength="100" required>  
        <label for="floatingInput" class="fw-bold"><small>Add new category</small></label>
      </div>
      <button class="btn bg-body-tertiary link-body-emphasis border-0 py-2 fw-bold mb-5 w-100 rounded-4" type="submit" name="submit">Submit</button>
    </form>
    <div class="container">
      <h4 class="mb-3">Categories</h4>
      <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-xxl-6 g-1">
        <?php foreach ($categories as $category): ?>
          <div class="col">
            <div class="card border-0 shadow bg-body-tertiary rounded-4">
              <div class="card-body p-4 fw-medium position-relative">
                <a class="text-decoration-none link-body-emphasis" href="category.php?q=<?php echo urlencode($category['category_name']); ?>">
                  <h6 class="link-body-emphasis text-start"><i class="bi bi-tags-fill"></i> <?php echo (!is_null($category['category_name']) && strlen($category['category_name']) > 20) ? substr($category['category_name'], 0, 20) . '...' : str_replace('_', ' ', $category['category_name']); ?></h6>
                </a>
                <div class="dropdown z-3 position-absolute end-0 top-50 translate-middle-y me-2">
                  <button class="btn border-0 link-body-emphasis" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical"></i>
                  </button>
                  <ul class="dropdown-menu z-3">
                    <li><button type="button" data-bs-toggle="modal" data-bs-target="#updateModal_<?php echo $category['category_id']; ?>" class="dropdown-item fw-bold"><i class="bi bi-pencil-fill"></i> edit</button></li>
                    <li><button type="button" data-bs-toggle="modal" data-bs-target="#deleteModal_<?php echo $category['category_id']; ?>" class="dropdown-item fw-bold"><i class="bi bi-trash-fill"></i> delete</button></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
          <!-- Delete Modal -->
          <div class="modal fade" id="deleteModal_<?php echo $category['category_id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content rounded-4 border-0">
                <div class="modal-body p-4 text-center fw-medium">
                  <h5 class="mb-0">Do you want to delete "<?php echo str_replace('_', ' ', $category['category_name']); ?>"?</h5>
                  <p class="mb-0 mt-2">You can't restore after delete the category.</p>
                </div>
                <form method="post">
                  <div class="modal-footer flex-nowrap p-0">
                    <input type="hidden" name="category_name_to_delete" value="<?php echo $category['category_name']; ?>">
                    <button class="btn btn-lg btn-link text-danger fs-6 text-decoration-none col-6 py-3 m-0 rounded-0 border-end" type="submit" name="delete_category" value="<?php echo $category['category_name']; ?>"><strong>Yes, delete this category!</strong></button>
                    <button type="button" class="btn btn-lg btn-link fs-6 text-decoration-none col-6 py-3 m-0 rounded-0" data-bs-dismiss="modal">Cancel, keep it!</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Update Modal -->
          <div class="modal fade" id="updateModal_<?php echo $category['category_id']; ?>" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                  <h5 class="modal-title" id="updateModalLabel">Update this category</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <!-- Update category form -->
                  <form method="post">
                    <div class="mb-3">
                      <label for="update_category_name" class="form-label">New Category Name</label>
                      <input type="text" class="form-control" id="update_category_name" name="update_category_name" value="<?php echo $category['category_name']; ?>" required>
                    </div>
                    <input type="hidden" name="category_id_to_update" value="<?php echo $category['category_id']; ?>">
                    <button type="submit" class="btn btn-primary w-100 fw-bold" name="update_category">Update</button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="mt-5"></div>
    <?php include('../../bootstrapjs.php'); ?>
  </body>
</html>
