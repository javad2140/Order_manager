<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
// This check is now handled by public/index.php, but keeping it here as a fallback or for direct access
if (!isset($_SESSION['user_id'])) {
    // Ensure BASE_URL is defined before using it for redirection
    if (!defined('BASE_URL')) {
        // Fallback definition if BASE_URL is not defined (should be defined in public/index.php)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script_name = $_SERVER['SCRIPT_NAME'];
        $base_path = dirname($script_name);
        if ($base_path !== '/' && substr($base_path, -1) !== '/') {
            $base_path .= '/';
        } elseif ($base_path === '/') {
            $base_path = '/';
        }
        define('BASE_URL', $protocol . "://" . $host . $base_path);
    }
    header("Location: " . BASE_URL . "auth/login");
    exit();
}

// Get username from session for display
$loggedInUsername = $_SESSION['username'] ?? 'کاربر مهمان'; // Default value if not set

// Get the current path for active link highlighting in header
$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$script_name_dir = trim(dirname($_SERVER['SCRIPT_NAME']), '/');
if (!empty($script_name_dir) && strpos($path, $script_name_dir) === 0) {
    $path = trim(substr($path, strlen($script_name_dir)), '/');
}
// For root path, normalize to 'dashboard' or empty string if it's the actual root
if ($path === '' || $path === '/') {
    $path = 'dashboard';
}

?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل مدیریت سفارشات</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>jalalidatepicker.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    </head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard">پنل مدیریت سفارشات</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'dashboard') ? 'active' : ''; ?>" aria-current="page" href="<?php echo BASE_URL; ?>dashboard">
                            <i class="fas fa-tachometer-alt"></i> داشبورد
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'products') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>products">
                            <i class="fas fa-box"></i> محصولات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'categories') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>categories">
                            <i class="fas fa-tags"></i> دسته‌بندی‌ها
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'orders') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>orders">
                            <i class="fas fa-shopping-basket"></i> سفارشات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'customers') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>customers">
                            <i class="fas fa-users"></i> مشتریان
                        </a>
                    </li>
                    <!-- [جدید] لینک بکاپ‌گیری اضافه شد -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($path === 'backup') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>backup">
                            <i class="fas fa-database"></i> بکاپ گیری
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> خوش آمدید، <?php echo htmlspecialchars($loggedInUsername); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item <?php echo ($path === 'auth/profile') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>auth/profile">
                                <i class="fas fa-user-edit"></i> تغییر رمز عبور
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout">
                                <i class="fas fa-sign-out-alt"></i> خروج
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
