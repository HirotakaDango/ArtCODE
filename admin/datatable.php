<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: ../admin/access.php');
    exit;
} 

class DataTable {
  private $pdo;
  private $table_name;

  public function __construct($db_file, $table_name) {
    try {
      $this->pdo = new PDO("sqlite:" . $db_file);
      $this->table_name = $table_name;
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die("Could not connect to database: " . $e->getMessage());
    }
  }

  public function get_all_data() {
    $stmt = $this->pdo->prepare("SELECT * FROM $this->table_name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function insert_data($data) {
    $columns = implode(',', array_keys($data));
    $values = ":" . implode(',:', array_keys($data));
    $stmt = $this->pdo->prepare("INSERT INTO $this->table_name ($columns) VALUES ($values)");
    $stmt->execute($data);
  }

  public function get_data_by_id($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM $this->table_name WHERE id=:id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public function update_data($data) {
    $set_values = '';
    foreach ($data as $key => $value) {
      if ($key !== 'id') {
        $set_values .= "$key=:$key, ";
      }
    }
    $set_values = rtrim($set_values, ', ');
    $stmt = $this->pdo->prepare("UPDATE $this->table_name SET $set_values WHERE id=:id");
    $stmt->execute($data);
  }

  public function delete_data($id) {
    $stmt = $this->pdo->prepare("DELETE FROM $this->table_name WHERE id=:id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
  }
}
?>
