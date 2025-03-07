<?php
require_once "model/UserModel.php";
require_once "view/helpers.php";

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // Hiển thị form đăng ký
    public function registerForm()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['user_role']);
        }

        $title = "Đăng ký";
        ob_start();
        include "view/users/user_register.blade.php";
        $content = ob_get_clean();
        include "view/layouts/master.blade.php";
    }

    // Xử lý đăng ký
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
            $role = $_POST['role'] ?? 'user'; // Lấy role từ form hoặc gán mặc định là 'user'

            if ($this->userModel->createUser($name, $email, $password, $role)) {
                header("Location: /login");
                exit;
            } else {
                echo "Đăng ký không thành công. Vui lòng thử lại.";
            }
        }
    }

    // Hiển thị form đăng nhập
    public function loginForm()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectBasedOnRole($_SESSION['user_role']);
        }

        $title = "Đăng nhập";
        ob_start();
        include "view/users/user_login.blade.php";
        $content = ob_get_clean();
        include "view/layouts/master.blade.php";
    }

    // Xử lý đăng nhập
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role']; // Lưu role vào session

                $this->redirectBasedOnRole($user['role']);
            } else {
                echo "Email hoặc mật khẩu không chính xác.";
            }
        }
    }

    // Xử lý đăng xuất
    public function logout()
    {
        session_start();
        session_destroy();
        header("Location: /login");
        exit;
    }

    // Hiển thị thông tin tài khoản
    public function accountInfo()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
                if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $user = $this->userModel->getUserById($userId);

            if ($user) {
                $title = "Thông tin tài khoản";
                ob_start();
                include "view/users/user_info.blade.php";
                $content = ob_get_clean();
                include "view/layouts/master.blade.php";
            } else {
                echo "Không tìm thấy thông tin tài khoản.";
            }
        } else {
            header("Location: /login");
            exit;
        }
    }

    // Cập nhật thông tin tài khoản
    public function updateAccount()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if ($this->userModel->updateUser($_SESSION['user_id'], $name, $email, $phone, $address)) {
                header("Location: /account");
                exit;
            } else {
                echo "Cập nhật không thành công. Vui lòng thử lại.";
            }
        }
    }

    // Xóa tài khoản
    public function deleteAccount()
    {
        session_start();
        if (isset($_SESSION['user_id'])) {
            if ($this->userModel->deleteUser($_SESSION['user_id'])) {
                session_destroy();
                header("Location: /register");
                exit;
            } else {
                echo "Xóa tài khoản không thành công.";
            }
        }
    }

    // Chuyển hướng dựa trên role
    private function redirectBasedOnRole($role)
    {
        if ($role === 'admin') {
            header("Location: /products");
        } else {
            header("Location: /product-list-user");
        }
        exit;
    }
}
