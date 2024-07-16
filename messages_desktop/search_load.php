<?php
require_once('../auth.php');

// Connect to the SQLite database using parameterized query
$db = new SQLite3('../database.sqlite');

$query = '';
if(isset($_POST['query'])) {
  $query = $_POST['query'];
}

$sql = "SELECT * FROM users WHERE artist LIKE :query OR id LIKE :query";
$stmt = $db->prepare($sql);
$stmt->bindValue(':query', '%' . $query . '%', SQLITE3_TEXT);

$result = $stmt->execute();

// Start output buffering to capture HTML
ob_start();
?>

<?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
  <a class="text-decoration-none text-<?php include($_SERVER['DOCUMENT_ROOT'] . '/appearance/opposite.php'); ?>" href="send.php?userid=<?php echo $row['id']; ?>" target="chatFrame" onclick="return false;">
    <div class="card p-3 rounded-4 bg-body-tertiary shadow my-2 border-0">
      <div class="d-flex align-items-center">
        <div class="d-inline-flex align-items-center justify-content-center me-3">
          <img id="previewImage" src="<?php echo ($row['pic'] ? $row['pic'] : "../icon/propic.png"); ?>" alt="Profile Picture" style="width: 96px; height: 96px;" class="border border-4 rounded-circle object-fit-cover">
        </div>
        <div>
          <h5 class="fw-bold"><?php echo $row['artist']; ?></h5>
          <p class="mb-2"><strong>User ID:</strong> <?php echo $row['id']; ?></p>
        </div>
      </div>
    </div>
  </a>
<?php endwhile; ?>

<?php
// End output buffering and capture the output
$output = ob_get_clean();
echo $output;
?>
