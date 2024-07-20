<?php
// Connect to the SQLite database
$dbNavPath = $_SERVER['DOCUMENT_ROOT'] . '/database.sqlite';
$dbNav = new SQLite3($dbNavPath);

// Get the admin details from the session
$adminIdNav = $_SESSION['admin']['id'];
$stmtNav = $dbNav->prepare("SELECT email, status FROM admin WHERE id = :id");
$stmtNav->bindValue(':id', $adminIdNav);
$resultNav = $stmtNav->execute();
$adminNav = $resultNav->fetchArray();

$emailNav = $adminNav['email'];
$statusNav = $adminNav['status'];

// Get the artist details from the users table
$stmtNav1 = $dbNav->prepare("SELECT id, artist, bgpic, pic FROM users WHERE email = :email");
$stmtNav1->bindValue(':email', $emailNav);
$resultNav1 = $stmtNav1->execute();
$rowNav1 = $resultNav1->fetchArray();
$picNav1 = $rowNav1['pic'];
$artistNav1 = $rowNav1['artist'];
?>

<header class="px-4 navbar bg-dark flex-md-nowrap align-items-center p-0 shadow py-2" data-bs-theme="dark">
  <a class="me-auto fs-4 text-white fw-bold text-decoration-none" href="/admin/">ArtCODE</a>
  <h4 class="ms-auto text-white fw-bold">Welcome, <?php echo htmlspecialchars($artistNav1); ?>! <img style="width: 32px; height: 32px;" src="/<?php echo $picNav1; ?>" class="rounded-circle" alt="Profile Picture"></h4>
</header>
