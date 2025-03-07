<?php
require_once "Database.php";

class UserModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Tạo người dùng mới
    public function createUser($name, $email, $password, $role) {
        // Kiểm tra email đã tồn tại chưa
        if ($this->getUserByEmail($email)) {
            return false; // Email đã tồn tại
        }

        $query = "INSERT INTO users (name, email, password, role, created_at) 
                  VALUES (:name, :email, :password, :role, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password); // Password đã được hash bên ngoài
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    // Lấy người dùng theo email
    public function getUserByEmail($email) {
        $query = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về dữ liệu người dùng
    }

    // Lấy người dùng theo ID
    public function getUserById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật thông tin người dùng
    public function updateUser($id, $name, $email, $phone, $address, $role = null) {
        $query = "UPDATE users 
                  SET name = :name, email = :email, phone = :phone, address = :address";
        if ($role !== null) {
            $query .= ", role = :role"; // Cập nhật role nếu có
        }
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':address', $address);
        if ($role !== null) {
            $stmt->bindParam(':role', $role);
        }
        return $stmt->execute();
    }

    // Xóa người dùng
    public function deleteUser($id) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
