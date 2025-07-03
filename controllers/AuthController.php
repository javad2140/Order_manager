<?php
// No session_start() here, as it's handled by the central router (public/index.php)
// No ini_set or error_reporting here, as it's handled by public/index.php

// Corrected Path: From controllers/ to core/
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php'; // Database is needed to pass to Auth class

class AuthController {
    private $db;
    private $auth;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new Auth($this->db);
    }

    public function login() {
        // Define and initialize variables that will be passed to the view
        $pageTitle = "";
        $error_message = "";
        $success_message = "";

        // [FIXED]: استفاده از BASE_URL برای ریدایرکت به dashboard
        // اگر کاربر قبلاً لاگین کرده است، به داشبورد هدایت شود
        if ($this->auth->isLoggedIn()) {
            header("Location: " . BASE_URL . "dashboard"); // [FIXED]
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username_input = trim($_POST['username']);
            $password_input = trim($_POST['password']);

            if (empty($username_input) || empty($password_input)) {
                $error_message = 'لطفا نام کاربری و رمز عبور را وارد کنید.';
            } else {
                if ($this->auth->login($username_input, $password_input)) {
                    session_regenerate_id(true);
                    // [FIXED]: استفاده از BASE_URL برای ریدایرکت پس از لاگین موفق
                    header("Location: " . BASE_URL . "dashboard"); // [FIXED]
                    exit();
                } else {
                    $error_message = 'نام کاربری یا رمز عبور اشتباه است.';
                }
            }
        }
        $pageTitle = "ورود به پنل مدیریت";
        // Extract variables to make them available in the included view
        extract(compact('pageTitle', 'error_message', 'success_message'));
        
        // Include the view for login - Path relative from controllers/ to views/auth/
        include __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        // Define and initialize variables that will be passed to the view
        $pageTitle = "";
        $success_message = "";
        $error_message = "";
        $username = ""; // To preserve input on error
        $email = "";    // To preserve input on error

        // [FIXED]: استفاده از BASE_URL برای ریدایرکت به dashboard
        // اگر کاربر قبلاً لاگین کرده است، به داشبورد هدایت شود
        if ($this->auth->isLoggedIn()) {
            header("Location: " . BASE_URL . "dashboard"); // [FIXED]
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username_input = trim($_POST['username']);
            $password_input = trim($_POST['password']);
            $email_input = trim($_POST['email']);
            $role = 'admin';

            if (empty($username_input) || empty($password_input) || empty($email_input)) {
                $error_message = 'لطفا تمام فیلدهای الزامی را پر کنید.';
            } elseif (strlen($password_input) < 6) {
                $error_message = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            } elseif (!filter_var($email_input, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'فرمت ایمیل نامعتبر است.';
            } else {
                try {
                    // Check if username already exists using the Auth class (or directly here if Auth doesn't handle it)
                    // The Auth::register method in the provided Auth.php already handles checking for existing username
                    // and inserts the user. We just need to call it.
                    if ($this->auth->register($username_input, $password_input, $email_input, $role)) {
                        $success_message = 'حساب کاربری با موفقیت ایجاد شد. اکنون می‌توانید وارد شوید.';
                        // No need to preserve input on success, as form will reset implicitly
                    } else {
                        // If Auth::register returns false, it means registration failed (e.g., username already exists)
                        $error_message = 'نام کاربری قبلا ثبت شده است. لطفا نام کاربری دیگری انتخاب کنید.';
                        $username = $username_input; // Preserve input
                        $email = $email_input;       // Preserve input
                    }
                } catch (PDOException $e) {
                    error_log("Registration error: " . $e->getMessage());
                    $error_message = 'خطای دیتابیس در ثبت نام: ' . $e->getMessage();
                    $username = $username_input; // Preserve input
                    $email = $email_input;       // Preserve input
                }
            }
        }
        $pageTitle = "ثبت نام مدیر جدید";
        // Extract variables to make them available in the included view
        extract(compact('pageTitle', 'error_message', 'success_message', 'username', 'email'));
        
        // Include the view for register - Path relative from controllers/ to views/auth/
        include __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        $this->auth->logout();
        // [FIXED]: استفاده از BASE_URL برای ریدایرکت پس از خروج
        header("Location: " . BASE_URL . "auth/login"); // Redirect to login page after logout
        exit();
    }

    public function profile() {
        // این متد صفحه پروفایل را نمایش می‌دهد.
        // اگر کاربر لاگین نکرده باشد، به صفحه لاگین هدایت می‌شود (توسط router در public/index.php کنترل می‌شود).
        $pageTitle = "پروفایل مدیر";
        $success_message = $_SESSION['success_message'] ?? '';
        $error_message = $_SESSION['error_message'] ?? '';

        // پاک کردن پیام‌ها از سشن پس از نمایش
        unset($_SESSION['success_message']);
        unset($_SESSION['error_message']);

        // متغیرها را برای view آماده کنید
        extract(compact('pageTitle', 'success_message', 'error_message'));

        // شامل کردن view پروفایل
        include __DIR__ . '/../views/auth/profile.php';
    }

    public function changePassword() {
        // این متد درخواست POST برای تغییر رمز عبور را پردازش می‌کند.
        // اگر کاربر لاگین نکرده باشد، به صفحه لاگین هدایت می‌شود.
        $error_message = '';
        $success_message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current_password = trim($_POST['current_password'] ?? '');
            $new_password = trim($_POST['new_password'] ?? '');
            $confirm_new_password = trim($_POST['confirm_new_password'] ?? '');
            $user_id = $_SESSION['user_id'] ?? null; // شناسه کاربر از سشن

            if (!$user_id) {
                $error_message = 'شما وارد نشده‌اید.';
            } elseif (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
                $error_message = 'لطفاً تمام فیلدها را پر کنید.';
            } elseif (strlen($new_password) < 6) {
                $error_message = 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.';
            } elseif ($new_password !== $confirm_new_password) {
                $error_message = 'رمز عبور جدید با تایید آن مطابقت ندارد.';
            } else {
                // فراخوانی متد changePassword از مدل Auth
                if ($this->auth->changePassword($user_id, $current_password, $new_password)) {
                    $success_message = 'رمز عبور شما با موفقیت تغییر یافت.';
                } else {
                    $error_message = 'رمز عبور فعلی اشتباه است یا خطایی در تغییر رمز رخ داد.';
                }
            }
        } else {
            $error_message = 'درخواست نامعتبر.';
        }

        // ذخیره پیام‌ها در سشن برای نمایش پس از ریدایرکت
        $_SESSION['success_message'] = $success_message;
        $_SESSION['error_message'] = $error_message;

        // ریدایرکت به صفحه پروفایل برای نمایش پیام‌ها
        header("Location: " . BASE_URL . "auth/profile");
        exit();
    }
}
