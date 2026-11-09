<?php
/**
 * Andmebaasiühendus (PDO). Kõik päringud käivad läbi ettevalmistatud
 * lausete (prepared statements), et vältida SQL-süstimist.
 */
class Database {
    private $conn;
    private $host;
    private $user;
    private $password;
    private $baseName;

    function __construct() {
        $this->host     = getenv('DB_HOST') ?: 'db';
        $this->user     = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'root';
        $this->baseName = getenv('DB_NAME') ?: 'ilusalong';
        $this->connect();
    }

    function __destruct() {
        $this->disconnect();
    }

    function connect() {
        if (!$this->conn) {
            try {
                $this->conn = new PDO(
                    'mysql:host=' . $this->host . ';dbname=' . $this->baseName . ';charset=utf8mb4',
                    $this->user,
                    $this->password,
                    array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
                );
            } catch (Exception $e) {
                die('Connection failed : ' . $e->getMessage());
            }
        }
        return $this->conn;
    }

    function disconnect() {
        $this->conn = null;
    }

    // Üks rida
    function getOne($query, $params = array()) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Kõik read
    function getAll($query, $params = array()) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // INSERT / UPDATE / DELETE — tagastab muudetud ridade arvu
    function executeRun($query, $params = array()) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    function lastInsertId() {
        return $this->conn->lastInsertId();
    }
}
