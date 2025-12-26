<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'hotelgrandguardi_wedding_bliss';
    private $username = 'hotelgrandguardi_root';
    private $password = 'Sun123flower@';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}
?>