<?php
$id = $_GET['album'];

header("Location: /album/?id=" . $id);
exit();
?>