<?php
// controllers/CustomerController.php

// نیاز به شامل کردن مدل Database و Customer
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Customer.php';

class CustomerController {
    private $db;
    private $customer_obj;

    public function __construct($db) {
        $this->db = $db;
        $this->customer_obj = new Customer($this->db);
    }

    public function index() {
        $pageTitle = "مدیریت مشتریان";
        $success_message = '';
        $error_message = '';
        $customer_to_edit = null; 

        // متغیرهای فیلتر و جستجو
        $search_term = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
        $state_filter = isset($_GET['state_filter']) && $_GET['state_filter'] !== '' ? htmlspecialchars(trim($_GET['state_filter'])) : null;
        $sort_by = isset($_GET['sort_by']) ? htmlspecialchars(trim($_GET['sort_by'])) : 'created_at';
        $sort_order = isset($_GET['sort_order']) ? htmlspecialchars(trim(strtoupper($_GET['sort_order']))) : 'DESC';

        // منطق صفحه‌بندی
        $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $records_per_page = 10; // هر صفحه ۱۰ رکورد
        $from_record_num = ($records_per_page * $page) - $records_per_page;


        // متغیرهای موقت برای نگهداری اطلاعات فرم در صورت خطای اعتبارسنجی
        $form_data_add = []; 
        $form_data_edit = []; 
        $open_add_modal = false; 
        $open_edit_modal = false; 


        // مدیریت ارسال فرم افزودن/ویرایش/حذف مشتری
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // [NEW] بازیابی پارامترهای صفحه‌بندی و فیلتر از ورودی‌های مخفی POST
            $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
            $search_term = isset($_POST['search']) ? htmlspecialchars(trim($_POST['search'])) : '';
            $state_filter = isset($_POST['state_filter']) && $_POST['state_filter'] !== '' ? htmlspecialchars(trim($_POST['state_filter'])) : null;
            $sort_by = isset($_POST['sort_by']) ? htmlspecialchars(trim($_POST['sort_by'])) : 'created_at';
            $sort_order = isset($_POST['sort_order']) ? htmlspecialchars(trim(strtoupper($_POST['sort_order']))) : 'DESC';

            $should_redirect = false; 

            switch ($_POST['action']) {
                case 'add_customer':
                    $this->customer_obj->first_name = trim($_POST['first_name'] ?? '');
                    $this->customer_obj->last_name = trim($_POST['last_name'] ?? '');
                    $this->customer_obj->email = trim($_POST['email'] ?? ''); 
                    $this->customer_obj->instagram_id = trim($_POST['instagram_id'] ?? ''); 
                    $this->customer_obj->phone = trim($_POST['phone'] ?? ''); 
                    $this->customer_obj->postal_code = trim($_POST['postal_code'] ?? '');
                    $this->customer_obj->state = trim($_POST['state'] ?? ''); 
                    $this->customer_obj->city = trim($_POST['city'] ?? ''); 
                    $this->customer_obj->address_detail = trim($_POST['address_detail'] ?? ''); 

                    // ذخیره اطلاعات فرم در متغیر موقت برای نگهداری در صورت خطا
                    $form_data_add = [
                        'first_name' => $this->customer_obj->first_name,
                        'last_name' => $this->customer_obj->last_name,
                        'email' => $this->customer_obj->email,
                        'instagram_id' => $this->customer_obj->instagram_id,
                        'phone' => $this->customer_obj->phone,
                        'postal_code' => $this->customer_obj->postal_code,
                        'state' => $this->customer_obj->state,
                        'city' => $this->customer_obj->city,
                        'address_detail' => $this->customer_obj->address_detail,
                    ];

                    $validation_error_found = false;

                    if (!empty($this->customer_obj->email) && !filter_var($this->customer_obj->email, FILTER_VALIDATE_EMAIL)) {
                        $error_message = "فرمت ایمیل نامعتبر است.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->phone) && !preg_match('/^09[0-9]{9}$/', $this->customer_obj->phone)) {
                        $error_message = "شماره همراه معتبر نیست (باید ۱۱ رقمی و با ۰۹ شروع شود).";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->postal_code) && !preg_match('/^[0-9]{10}$/', $this->customer_obj->postal_code)) {
                        $error_message = "کد پستی باید ۱۰ رقمی باشد.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->email) && $this->customer_obj->emailExists($this->customer_obj->email)) {
                        $error_message = "ایمیل وارد شده قبلاً ثبت شده است. لطفاً ایمیل دیگری وارد کنید.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->instagram_id) && $this->customer_obj->instagramIdExists($this->customer_obj->instagram_id)) {
                        $error_message = "آی‌دی اینستاگرام وارد شده قبلاً ثبت شده است. لطفاً آی‌دی دیگری وارد کنید.";
                        $validation_error_found = true;
                    } 
                    
                    if (!$validation_error_found) {
                        if ($this->customer_obj->create()) {
                            $success_message = "مشتری با موفقیت اضافه شد.";
                            $should_redirect = true; 
                        } else {
                            $error_message = "خطا در اضافه کردن مشتری. لطفا دوباره تلاش کنید. (خطای پایگاه داده)";
                        }
                    } else {
                        $open_add_modal = true; 
                    }
                    break;

                case 'edit_customer': 
                    $this->customer_obj->id = $_POST['customer_id'];
                    $this->customer_obj->first_name = trim($_POST['edit_first_name'] ?? '');
                    $this->customer_obj->last_name = trim($_POST['edit_last_name'] ?? '');
                    $this->customer_obj->email = trim($_POST['edit_email'] ?? '');
                    $this->customer_obj->instagram_id = trim($_POST['edit_instagram_id'] ?? '');
                    $this->customer_obj->phone = trim($_POST['edit_phone'] ?? '');
                    $this->customer_obj->postal_code = trim($_POST['edit_postal_code'] ?? '');
                    $this->customer_obj->state = trim($_POST['edit_state'] ?? '');
                    $this->customer_obj->city = trim($_POST['edit_city'] ?? '');
                    $this->customer_obj->address_detail = trim($_POST['edit_address_detail'] ?? '');

                    $form_data_edit = [
                        'id' => $this->customer_obj->id,
                        'first_name' => $this->customer_obj->first_name,
                        'last_name' => $this->customer_obj->last_name,
                        'email' => $this->customer_obj->email,
                        'instagram_id' => $this->customer_obj->instagram_id,
                        'phone' => $this->customer_obj->phone,
                        'postal_code' => $this->customer_obj->postal_code,
                        'state' => $this->customer_obj->state,
                        'city' => $this->customer_obj->city,
                        'address_detail' => $this->customer_obj->address_detail,
                    ];

                    $validation_error_found = false;

                    if (!empty($this->customer_obj->email) && !filter_var($this->customer_obj->email, FILTER_VALIDATE_EMAIL)) {
                        $error_message = "فرمت ایمیل نامعتبر است.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->phone) && !preg_match('/^09[0-9]{9}$/', $this->customer_obj->phone)) {
                        $error_message = "شماره همراه معتبر نیست (باید ۱۱ رقمی و با ۰۹ شروع شود).";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->postal_code) && !preg_match('/^[0-9]{10}$/', $this->customer_obj->postal_code)) {
                        $error_message = "کد پستی باید ۱۰ رقمی باشد.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->email) && $this->customer_obj->emailExists($this->customer_obj->email, $this->customer_obj->id)) {
                        $error_message = "ایمیل وارد شده قبلاً برای مشتری دیگری ثبت شده است. لطفاً ایمیل دیگری وارد کنید.";
                        $validation_error_found = true;
                    } 
                    elseif (!empty($this->customer_obj->instagram_id) && $this->customer_obj->instagramIdExists($this->customer_obj->instagram_id, $this->customer_obj->id)) {
                        $error_message = "آی‌دی اینستاگرام وارد شده قبلاً برای مشتری دیگری ثبت شده است. لطفاً آی‌دی دیگری وارد کنید.";
                        $validation_error_found = true;
                    } 
                    
                    if (!$validation_error_found) {
                        if ($this->customer_obj->update()) {
                            $success_message = "مشتری با موفقیت ویرایش شد.";
                            $should_redirect = true; 
                        } else {
                            $error_message = "خطا در ویرایش مشتری. لطفا دوباره تلاش کنید. (خطای پایگاه داده)";
                        }
                    } else {
                        $open_edit_modal = true; 
                    }
                    break;
                
                case 'delete_customer':
                    $this->customer_obj->id = $_POST['delete_customer_id'];
                    if ($this->customer_obj->delete()) {
                        $success_message = "مشتری با موفقیت حذف شد.";
                        // [NEW] تنظیم مجدد صفحه برای حذف مشتری (مانند ProductsController)
                        $total_rows_after_delete = $this->customer_obj->countAll($search_term, $state_filter); 
                        $records_per_page_check = 10; // Use a fixed records_per_page for this check
                        $total_pages_after_delete = ceil($total_rows_after_delete / $records_per_page_check);

                        if ($page > $total_pages_after_delete && $total_pages_after_delete > 0 && $page > 1) { 
                            $page--; 
                        } elseif ($total_pages_after_delete === 0) { 
                            $page = 1; 
                        }
                        $should_redirect = true;
                    } else {
                        $error_message = "خطا در حذف مشتری. ممکن است سفارشاتی به این مشتری مرتبط باشند.";
                    }
                    break;
            }

            if ($should_redirect) {
                $_SESSION['success_message'] = $success_message;
                $_SESSION['error_message'] = $error_message; 
                unset($_SESSION['form_data_add']);
                unset($_SESSION['form_data_edit']); 
                unset($_SESSION['open_add_modal']);
                unset($_SESSION['open_edit_modal']);

                // [NEW] ساخت URL ریدایرکت با حفظ پارامترهای فیلتر و صفحه‌بندی
                $redirect_url = "customers?page=" . $page; 
                if (!empty($search_term)) $redirect_url .= "&search=" . urlencode($search_term);
                if ($state_filter !== null) $redirect_url .= "&state_filter=" . urlencode($state_filter);
                if (!empty($sort_by)) $redirect_url .= "&sort_by=" . urlencode($sort_by);
                if (!empty($sort_order)) $redirect_url .= "&sort_order=" . urlencode($sort_order);

                header("Location: " . BASE_URL . $redirect_url); 
                exit();
            } else {
                if ($open_add_modal) {
                    $_SESSION['form_data_add'] = $form_data_add;
                    $_SESSION['open_add_modal'] = true;
                } elseif ($open_edit_modal) { 
                    $_SESSION['form_data_edit'] = $form_data_edit;
                    $_SESSION['open_edit_modal'] = true;
                }
                $_SESSION['error_message'] = $error_message;
            }
        }

        // واکشی پیام‌های موفقیت/خطا از سشن
        if (isset($_SESSION['success_message'])) {
            $success_message = $_SESSION['success_message'];
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            $error_message = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
        }

        // واکشی اطلاعات فرم و وضعیت باز بودن مودال از سشن در صورت وجود
        if (isset($_SESSION['form_data_add'])) {
            $form_data_add = $_SESSION['form_data_add'];
            unset($_SESSION['form_data_add']);
            $open_add_modal = true;
        }
        if (isset($_SESSION['open_add_modal'])) {
            $open_add_modal = true;
            unset($_SESSION['open_add_modal']);
        }

        if (isset($_SESSION['form_data_edit'])) {
            $customer_to_edit = $_SESSION['form_data_edit']; // داده‌ها از سشن
            unset($_SESSION['form_data_edit']);
            $open_edit_modal = true; 
        }
        if (isset($_SESSION['open_edit_modal'])) {
            $open_edit_modal = true;
            unset($_SESSION['open_edit_modal']);
        }


        // بررسی برای حالت ویرایش مشتری (وقتی ID مشتری از طریق GET می‌آید، مثلاً پس از ریدایرکت)
        if ($customer_to_edit === null && isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
            $customer_id_to_edit = (int)$_GET['edit_id'];
            $this->customer_obj->id = $customer_id_to_edit;
            $customer_to_edit = $this->customer_obj->readOne(); 

            if (!$customer_to_edit) {
                $error_message = "مشتری مورد نظر برای ویرایش یافت نشد.";
            } else {
                 $open_edit_modal = true; // [NEW] مودال ویرایش را باز نگه دار
            }
        }

        // [NEW] واکشی لیست مشتریان با صفحه‌بندی و فیلتر
        $customers_stmt = $this->customer_obj->readPaging($from_record_num, $records_per_page, $search_term, $state_filter, $sort_by, $sort_order);
        
        if ($customers_stmt === false) {
            $error_message = "خطا در واکشی مشتریان از پایگاه داده.";
            $num_customers = 0; // [NEW] تعداد مشتریان را 0 قرار می‌دهیم
            $customers_array = [];
        } else {
            $num_customers = $customers_stmt->rowCount(); // [NEW] تعداد مشتریان واکشی شده
            $customers_array = $customers_stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // [NEW] محاسبه کل صفحات برای صفحه‌بندی
        $total_rows = $this->customer_obj->countAll($search_term, $state_filter);
        $total_pages = ceil($total_rows / $records_per_page);

        // استان‌های ثابت برای فیلتر
        $all_states = [
            "آذربایجان شرقی", "آذربایجان غربی", "اردبیل", "اصفهان", "البرز", "ایلام", "بوشهر", "تهران",
            "چهارمحال و بختیاری", "خراسان جنوبی", "خراسان رضوی", "خراسان شمالی", "خوزستان", "زنجان",
            "سمنان", "سیستان و بلوچستان", "فارس", "قزوین", "قم", "کردستان", "کرمان", "کرمانشاه",
            "کهگیلویه و بویراحمد", "گلستان", "گیلان", "لرستان", "مازندران", "مرکزی", "هرمزگان", "همدان", "یزد"
        ];


        // استخراج متغیرها برای در دسترس قرار گرفتن در نمای شامل شده
        extract(compact('pageTitle', 'success_message', 'error_message', 'customers_array', 'customer_to_edit', 
                        'form_data_add', 'form_data_edit', 'open_add_modal', 'open_edit_modal',
                        'search_term', 'state_filter', 'sort_by', 'sort_order', 
                        'page', 'records_per_page', 'total_rows', 'total_pages', 'all_states'));

        // شامل کردن فایل هدر، نمای اصلی و فوتر
        include __DIR__ . '/../views/includes/header.php';
        include __DIR__ . '/../views/customers/index.php';
        include __DIR__ . '/../views/includes/footer.php';
    }
}
