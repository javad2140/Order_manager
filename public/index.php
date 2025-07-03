<?php

// Start session at the very beginning of the script
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL for consistent asset linking
// This method constructs the base URL dynamically based on the server environment.
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME']; // e.g., /order_management/public/index.php
$base_path = dirname($script_name); // e.g., /order_management/public

// Ensure base_path ends with a slash if it's not the root
if ($base_path !== '/' && substr($base_path, -1) !== '/') {
    $base_path .= '/';
} elseif ($base_path === '/') {
    // If it's root, ensure it's just a single slash
    $base_path = '/';
}

define('BASE_URL', $protocol . "://" . $host . $base_path);


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/OrderController.php';
require_once __DIR__ . '/../controllers/ProductController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/CustomerController.php';
// [جدید] کنترلر بکاپ اضافه شد
require_once __DIR__ . '/../controllers/BackupController.php';

// Initialize Database connection
$database = new Database();
$db = $database->getConnection();

// Get the current URI and parse it
$request_uri_full = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script_name_dir = dirname($_SERVER['SCRIPT_NAME']); // e.g., /order_management/public

// --- DEBUGGING START ---
error_log("DEBUG: Full REQUEST_URI: " . $request_uri_full);
error_log("DEBUG: SCRIPT_NAME_DIR: " . $script_name_dir);
// --- DEBUGGING END ---

// Remove the base path if the application is in a subdirectory
// Ensure $script_name_dir ends with a slash for consistent trimming
if (substr($script_name_dir, -1) !== '/') {
    $script_name_dir .= '/';
}

if (strpos($request_uri_full, $script_name_dir) === 0) {
    $request_uri = substr($request_uri_full, strlen($script_name_dir));
} else {
    // Fallback if script_name_dir doesn't match the start of request_uri_full
    $request_uri = $request_uri_full;
}

// Trim leading/trailing slashes
$request_uri = trim($request_uri, '/');

// --- DEBUGGING START ---
error_log("DEBUG: Processed request_uri (after trimming): " . $request_uri);
// --- DEBUGGING END ---


// Routing
$controller = null;
$method = null;
$params = [];

// Get the current path for active link highlighting in header
$path = $request_uri;

// Define routes
switch ($request_uri) {
    case '':
    case 'dashboard':
        // Pass the database connection to the DashboardController constructor
        $controller = new DashboardController($db); 
        $method = 'index';
        error_log("DEBUG: Matched route: dashboard");
        break;
    case 'auth/login':
        // Pass the database connection ($db) to AuthController,
        // so AuthController can create its own Auth instance with the correct $db.
        $controller = new AuthController($db); 
        $method = 'login';
        error_log("DEBUG: Matched route: auth/login");
        break;
    case 'auth/register':
        // Pass the database connection ($db) to AuthController
        $controller = new AuthController($db); 
        $method = 'register';
        error_log("DEBUG: Matched route: auth/register");
        break;
    case 'auth/logout':
        // Pass the database connection ($db) to AuthController
        $controller = new AuthController($db); 
        $method = 'logout';
        error_log("DEBUG: Matched route: auth/logout");
        break;
    case 'auth/profile': // NEW PROFILE ROUTE
        $controller = new AuthController($db);
        $method = 'profile';
        error_log("DEBUG: Matched route: auth/profile");
        break;
    case 'auth/change-password': // NEW CHANGE PASSWORD ROUTE (for POST requests)
        $controller = new AuthController($db);
        $method = 'changePassword';
        error_log("DEBUG: Matched route: auth/change-password");
        break;
    case 'orders':
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'index';
        error_log("DEBUG: Matched route: orders");
        break;
    case 'orders/store':
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'store';
        error_log("DEBUG: Matched route: orders/store");
        break;
    case 'orders/view': // For fetching order details via AJAX
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'view'; // You might need to implement a 'view' method in OrderController
        error_log("DEBUG: Matched route: orders/view");
        break;
    case (preg_match('/^orders\/edit\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'edit';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: orders/edit");
        break;
    case (preg_match('/^orders\/update\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'update';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: orders/update");
        break;
    case (preg_match('/^orders\/delete\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to OrderController constructor
        $controller = new OrderController($db); 
        $method = 'delete';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: orders/delete");
        break;
    case 'products':
        // Pass the database connection to ProductController constructor
        $controller = new ProductController($db); 
        $method = 'index';
        error_log("DEBUG: Matched route: products");
        break;
    case 'products/store':
        // Pass the database connection to ProductController constructor
        $controller = new ProductController($db); 
        $method = 'store';
        error_log("DEBUG: Matched route: products/store");
        break;
    case (preg_match('/^products\/edit\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to ProductController constructor
        $controller = new ProductController($db); 
        $method = 'edit';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: products/edit");
        break;
    case (preg_match('/^products\/update\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to ProductController constructor
        $controller = new ProductController($db); 
        $method = 'update';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: products/update");
        break;
    case (preg_match('/^products\/delete\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to ProductController constructor
        $controller = new ProductController($db); 
        $method = 'delete';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: products/delete");
        break;
    case 'categories':
        // Pass the database connection to CategoryController constructor
        $controller = new CategoryController($db); 
        $method = 'index';
        error_log("DEBUG: Matched route: categories");
        break;
    case 'categories/store':
        // Pass the database connection to CategoryController constructor
        $controller = new CategoryController($db); 
        $method = 'store';
        error_log("DEBUG: Matched route: categories/store");
        break;
    case (preg_match('/^categories\/edit\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CategoryController constructor
        $controller = new CategoryController($db); 
        $method = 'edit';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: categories/edit");
        break;
    case (preg_match('/^categories\/update\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CategoryController constructor
        $controller = new CategoryController($db); 
        $method = 'update';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: categories/update");
        break;
    case (preg_match('/^categories\/delete\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CategoryController constructor
        $controller = new CategoryController($db); 
        $method = 'delete';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: categories/delete");
        break;
    case 'customers':
        // Pass the database connection to CustomerController constructor
        $controller = new CustomerController($db); 
        $method = 'index';
        error_log("DEBUG: Matched route: customers");
        break;
    case 'customers/store':
        // Pass the database connection to CustomerController constructor
        $controller = new CustomerController($db); 
        $method = 'store';
        error_log("DEBUG: Matched route: customers/store");
        break;
    case (preg_match('/^customers\/edit\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CustomerController constructor
        $controller = new CustomerController($db); 
        $method = 'edit';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: customers/edit");
        break;
    case (preg_match('/^customers\/update\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CustomerController constructor
        $controller = new CustomerController($db); 
        $method = 'update';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: customers/update");
        break;
    case (preg_match('/^customers\/delete\/(\d+)$/', $request_uri, $matches) ? true : false):
        // Pass the database connection to CustomerController constructor
        $controller = new CustomerController($db); 
        $method = 'delete';
        $params[] = $matches[1];
        error_log("DEBUG: Matched route: customers/delete");
        break;

    // [جدید] روت‌های مربوط به بکاپ‌گیری
    case 'backup':
        $controller = new BackupController();
        $method = 'index';
        error_log("DEBUG: Matched route: backup");
        break;
    case 'backup/create':
        $controller = new BackupController();
        $method = 'create';
        error_log("DEBUG: Matched route: backup/create");
        break;
    case 'backup/restore':
        $controller = new BackupController();
        $method = 'restore';
        error_log("DEBUG: Matched route: backup/restore");
        break;
        
    default:
        http_response_code(404);
        echo "404 Not Found - Route not defined.";
        error_log("DEBUG: Matched route: 404 Not Found for URI: " . $request_uri);
        exit();
}

// Routes that do NOT require authentication
$public_routes = ['auth/login', 'auth/register'];

// Check if user is logged in for all routes EXCEPT public routes
// If the current request URI is NOT in the public_routes array AND the user is NOT logged in, then redirect to login.
if (!in_array($request_uri, $public_routes)) {
    // We need to create an Auth instance here to check isLoggedIn, since $controller might not be AuthController yet
    $temp_auth_checker = new Auth($db);
    $is_logged_in = $temp_auth_checker->isLoggedIn();
    error_log("DEBUG: Authentication check for " . $request_uri . ": Is Logged In? " . ($is_logged_in ? 'Yes' : 'No'));

    if (!$is_logged_in) {
        header("Location: " . BASE_URL . "auth/login");
        exit();
    }
}


// Execute controller method
if ($controller && method_exists($controller, $method)) {
    error_log("DEBUG: Executing controller " . get_class($controller) . "->" . $method . " with params: " . implode(', ', $params));
    call_user_func_array([$controller, $method], $params);
} else {
    http_response_code(404);
    echo "404 Not Found - Controller or method not found.";
    error_log("DEBUG: Controller or method not found for URI: " . $request_uri);
}
?>
