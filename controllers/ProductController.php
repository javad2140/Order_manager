<?php
// No session_start() here, as it's handled by the central router (public/index.php)
// No ini_set or error_reporting here, as it's handled by public/index.php

require_once __DIR__ . '/../core/Product.php';
require_once __DIR__ . '/../core/Category.php';
require_once __DIR__ . '/../core/Database.php'; // Required for database connection


class ProductController {
    private $db;
    private $product_obj;
    private $category_obj;

    public function __construct($db) {
        $this->db = $db;
        $this->product_obj = new Product($this->db);
        $this->category_obj = new Category($this->db);
    }

    public function index() {
        $success_message = '';
        $error_message = '';
        $product_to_edit = null; // برای نگهداری اطلاعات محصولی که قرار است ویرایش شود
        $pageTitle = "مدیریت محصولات";

        // Capture filter, search & sort parameters from GET request
        $search_term = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
        $category_filter_id = isset($_GET['category_filter']) && $_GET['category_filter'] !== '' ? (int)$_GET['category_filter'] : null;
        $status_filter = isset($_GET['status_filter']) && $_GET['status_filter'] !== '' ? (int)$_GET['status_filter'] : null;
        $sort_by = isset($_GET['sort_by']) ? htmlspecialchars(trim($_GET['sort_by'])) : 'created_at';
        $sort_order = isset($_GET['sort_order']) ? htmlspecialchars(trim(strtoupper($_GET['sort_order']))) : 'DESC';

        // Pagination Logic
        // Get current page number. Try $_POST first if form submitted (for redirects), then $_GET.
        $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $records_per_page = 10;
        $from_record_num = ($records_per_page * $page) - $records_per_page;

        // Get total number of records (before any deletion for accurate page calculation) - with filters
        $total_rows = $this->product_obj->countAll($search_term, $category_filter_id, $status_filter);

        // Handle form submissions (Add, Edit, Delete)
        // These will now receive 'current_page' and filter/sort parameters from hidden inputs
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Re-capture current page and filters from POST for redirection
            // These values come from hidden inputs in the forms
            $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
            $search_term = isset($_POST['search']) ? htmlspecialchars(trim($_POST['search'])) : '';
            $category_filter_id = isset($_POST['category_filter']) && $_POST['category_filter'] !== '' ? (int)$_POST['category_filter'] : null;
            $status_filter = isset($_POST['status_filter']) && $_POST['status_filter'] !== '' ? (int)$_POST['status_filter'] : null;
            $sort_by = isset($_POST['sort_by']) ? htmlspecialchars(trim($_POST['sort_by'])) : 'created_at';
            $sort_order = isset($_POST['sort_order']) ? htmlspecialchars(trim(strtoupper($_POST['sort_order']))) : 'DESC';


            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'add_product':
                        $this->product_obj->name = trim($_POST['product_name']);
                        $this->product_obj->sku = trim($_POST['product_sku'] ?? ''); 
                        $this->product_obj->price = trim($_POST['product_price_raw']); 
                        $this->product_obj->description = trim($_POST['product_description'] ?? ''); 
                        $this->product_obj->image_url = trim($_POST['product_image_url'] ?? ''); 
                        $this->product_obj->category_id = empty($_POST['product_category_id']) ? null : (int)$_POST['product_category_id'];
                        $this->product_obj->stock = (int)$_POST['product_stock'];
                        $this->product_obj->is_active = isset($_POST['product_is_active']) ? 1 : 0; 

                        if (empty($this->product_obj->name) || !is_numeric($this->product_obj->price)) { 
                            $error_message = "لطفا نام و قیمت معتبر برای محصول وارد کنید.";
                        } elseif ($this->product_obj->price < 0) {
                            $error_message = "قیمت محصول نمی‌تواند منفی باشد.";
                        } else {
                            if ($this->product_obj->create()) {
                                $success_message = "محصول با موفقیت اضافه شد.";
                            } else {
                                $error_message = "خطا در اضافه کردن محصول. لطفا دوباره تلاش کنید.";
                            }
                        }
                        break;

                    case 'edit_product':
                        $this->product_obj->id = $_POST['product_id'];
                        $this->product_obj->name = trim($_POST['edit_product_name']);
                        $this->product_obj->sku = trim($_POST['edit_product_sku'] ?? '');
                        $this->product_obj->price = trim($_POST['edit_product_price_raw']); 
                        $this->product_obj->description = trim($_POST['edit_product_description'] ?? '');
                        $this->product_obj->image_url = trim($_POST['edit_product_image_url'] ?? '');
                        $this->product_obj->category_id = empty($_POST['edit_product_category_id']) ? null : (int)$_POST['edit_product_category_id'];
                        $this->product_obj->stock = (int)$_POST['edit_product_stock'];
                        $this->product_obj->is_active = isset($_POST['edit_product_is_active']) ? 1 : 0;

                        if (empty($this->product_obj->name) || !is_numeric($this->product_obj->price)) {
                            $error_message = "لطفا نام و قیمت معتبر برای محصول وارد کنید.";
                        } elseif ($this->product_obj->price < 0) {
                            $error_message = "قیمت محصول نمی‌تواند منفی باشد.";
                        } else {
                            if ($this->product_obj->update()) {
                                $success_message = "محصول با موفقیت ویرایش شد.";
                            } else {
                                $error_message = "خطا در ویرایش محصول. لطفا دوباره تلاش کنید.";
                            }
                        }
                        break;

                    case 'delete_product':
                        $this->product_obj->id = $_POST['delete_product_id'];
                        if ($this->product_obj->delete()) {
                            $success_message = "محصول با موفقیت حذف شد.";
                            // Adjust page number after deletion to avoid empty pages
                            $total_rows_after_delete = $this->product_obj->countAll($search_term, $category_filter_id, $status_filter); 
                            $records_per_page_check = 10; // Use a fixed records_per_page for this check
                            $total_pages_after_delete = ceil($total_rows_after_delete / $records_per_page_check);

                            if ($page > $total_pages_after_delete && $total_pages_after_delete > 0 && $page > 1) { 
                                $page--; 
                            } elseif ($total_pages_after_delete === 0) { 
                                $page = 1; 
                            }
                        } else {
                            $error_message = "خطا در حذف محصول. لطفا دوباره تلاش کنید.";
                        }
                        break;
                }
                // Build redirect URL including current page and all filter/sort parameters
                $redirect_url = "products?page=" . $page; 
                if (!empty($search_term)) $redirect_url .= "&search=" . urlencode($search_term);
                if ($category_filter_id !== null && $category_filter_id !== '') $redirect_url .= "&category_filter=" . $category_filter_id;
                if ($status_filter !== null && ($status_filter == 0 || $status_filter == 1)) $redirect_url .= "&status_filter=" . $status_filter;
                if (!empty($sort_by)) $redirect_url .= "&sort_by=" . urlencode($sort_by);
                if (!empty($sort_order)) $redirect_url .= "&sort_order=" . urlencode($sort_order);

                header("Location: " . BASE_URL . $redirect_url); // Use BASE_URL here
                exit();
            }
        }

        // [New Logic]: Check if product_to_edit_php_data is available and open modal
        // This is for cases where the page reloads after an edit operation
        if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
            $product_id_to_edit = (int)$_GET['edit_id'];
            $this->product_obj->id = $product_id_to_edit;
            $product_to_edit = $this->product_obj->readOne(); // فراخوانی متد readOne از مدل Product

            if (!$product_to_edit) {
                $error_message = "محصول مورد نظر برای ویرایش یافت نشد.";
            }
        }


        // Re-read products for the current page (after any POST operations or initial GET)
        $products_stmt = $this->product_obj->readPaging($from_record_num, $records_per_page, $search_term, $category_filter_id, $status_filter, $sort_by, $sort_order);
        
        if ($products_stmt === false) { 
            $error_message = "خطا در واکشی محصولات از پایگاه داده. لطفاً لاگ‌های سرور را بررسی کنید.";
            $num_products = 0;
            $products_array = [];
        } else {
            $num_products = $products_stmt->rowCount();
            $products_array = $products_stmt->fetchAll(PDO::FETCH_ASSOC); 
        }
        
        // Recalculate total pages AFTER all POST operations for accurate display (if needed, already done above)
        // Ensure $total_rows is fresh after any operations that change product count
        $total_rows = $this->product_obj->countAll($search_term, $category_filter_id, $status_filter);
        $total_pages = ceil($total_rows / $records_per_page);


        $all_categories_flat = $this->category_obj->getAllCategoriesFlat();

        // Pass all necessary data to the view
        extract(compact(
            'pageTitle',
            'success_message',
            'error_message',
            'search_term',
            'category_filter_id',
            'status_filter',
            'sort_by',
            'sort_order',
            'page',
            'records_per_page',
            'from_record_num',
            'total_rows',
            'products_array',
            'num_products',
            'total_pages',
            'all_categories_flat',
            'product_to_edit' // این متغیر جدید است که اطلاعات محصول ویرایشی را نگه می‌دارد.
        ));

        // Path relative from controllers/ProductController.php to views/products/index.php
        include __DIR__ . '/../views/products/index.php';
        // [FIXED] تغییر مسیر فوتر
        require_once __DIR__ . '/../views/includes/footer.php'; 
    }
}
?>
