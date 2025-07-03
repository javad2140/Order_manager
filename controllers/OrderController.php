<?php
// controllers/OrderController.php

// نیاز به شامل کردن مدل Database و Order و OrderItem و Customer و Product
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Order.php';
require_once __DIR__ . '/../core/OrderItem.php';
require_once __DIR__ . '/../core/Customer.php';
require_once __DIR__ . '/../core/Product.php';
require_once __DIR__ . '/../jdf.php'; // Include the Jalali date functions

class OrderController {
    private $db;
    private $order_obj;
    private $order_item_obj;
    private $customer_obj;
    private $product_obj;

    public function __construct($db) {
        $this->db = $db;
        $this->order_obj = new Order($this->db); // Pass $db to Order model
        $this->order_item_obj = new OrderItem($this->db); // Pass $db to OrderItem model
        $this->customer_obj = new Customer($this->db); // Pass $db to Customer model
        $this->product_obj = new Product($this->db); // Pass $db to Product model
    }

    public function index() {
        $pageTitle = "مدیریت سفارشات";
        $success_message = '';
        $error_message = '';
        $order_to_edit = null; // برای نگهداری اطلاعات سفارشی که قرار است ویرایش شود

        // [NEW] متغیرهای فیلتر و جستجو برای سفارشات
        $search_term = isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : '';
        $customer_filter_id = isset($_GET['customer_filter']) && $_GET['customer_filter'] !== '' ? (int)$_GET['customer_filter'] : null;
        $status_filter = isset($_GET['status_filter']) && $_GET['status_filter'] !== '' ? htmlspecialchars(trim($_GET['status_filter'])) : null;
        $shipping_method_filter = isset($_GET['shipping_method_filter']) && $_GET['shipping_method_filter'] !== '' ? htmlspecialchars(trim($_GET['shipping_method_filter'])) : null;
        $sort_by = isset($_GET['sort_by']) ? htmlspecialchars(trim($_GET['sort_by'])) : 'created_at';
        $sort_order = isset($_GET['sort_order']) ? htmlspecialchars(trim(strtoupper($_GET['sort_order']))) : 'DESC';

        // [NEW] منطق صفحه‌بندی
        $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : (isset($_GET['page']) ? (int)$_GET['page'] : 1);
        $records_per_page = 10; // هر صفحه ۱۰ رکورد
        $from_record_num = ($records_per_page * $page) - $records_per_page;


        // متغیرهای موقت برای نگهداری اطلاعات فرم در صورت خطای اعتبارسنجی
        $form_data_add = [];
        $form_data_edit = [];
        $open_add_modal = false;
        $open_edit_modal = false;


        // مدیریت ارسال فرم افزودن/ویرایش/حذف سفارش
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            // [NEW] بازیابی پارامترهای صفحه‌بندی و فیلتر از ورودی‌های مخفی POST
            $page = isset($_POST['current_page']) ? (int)$_POST['current_page'] : 1;
            $search_term = isset($_POST['search']) ? htmlspecialchars(trim($_POST['search'])) : '';
            $customer_filter_id = isset($_POST['customer_filter']) && $_POST['customer_filter'] !== '' ? (int)$_POST['customer_filter'] : null;
            $status_filter = isset($_POST['status_filter']) && $_POST['status_filter'] !== '' ? htmlspecialchars(trim($_POST['status_filter'])) : null;
            $shipping_method_filter = isset($_POST['shipping_method_filter']) && $_POST['shipping_method_filter'] !== '' ? htmlspecialchars(trim($_POST['shipping_method_filter'])) : null;
            $sort_by = isset($_POST['sort_by']) ? htmlspecialchars(trim($_POST['sort_by'])) : 'created_at';
            $sort_order = isset($_POST['sort_order']) ? htmlspecialchars(trim(strtoupper($_POST['sort_order']))) : 'DESC';

            $should_redirect = false;

            switch ($_POST['action']) {
                case 'add_order':
                    $this->order_obj->customer_id = trim($_POST['customer_id'] ?? '');
                    $this->order_obj->order_date_shamsi = trim($_POST['order_date'] ?? '');
                    $this->order_obj->deposit_amount = trim($_POST['down_payment_raw'] ?? 0);
                    $this->order_obj->total_amount = trim($_POST['total_amount_raw'] ?? 0);
                    $this->order_obj->shipping_date = trim($_POST['estimated_delivery_date'] ?? '');
                    $this->order_obj->shipping_method = trim($_POST['shipping_method'] ?? '');
                    $this->order_obj->tracking_code = trim($_POST['tracking_code'] ?? '');
                    $this->order_obj->status = trim($_POST['order_status'] ?? 'processing'); // Default to 'processing'
                    $this->order_obj->notes = trim($_POST['admin_notes'] ?? '');

                    $product_ids = $_POST['product_ids'] ?? [];
                    $quantities = $_POST['quantities'] ?? [];
                    $prices_at_order = $_POST['prices_at_order'] ?? [];
                    $custom_descriptions = $_POST['custom_descriptions'] ?? [];

                    $form_data_add = [
                        'customer_id' => $this->order_obj->customer_id,
                        'order_date' => $this->order_obj->order_date_shamsi,
                        'down_payment_raw' => $this->order_obj->deposit_amount,
                        'total_amount_raw' => $this->order_obj->total_amount,
                        'estimated_delivery_date' => $this->order_obj->shipping_date,
                        'shipping_method' => $this->order_obj->shipping_method,
                        'tracking_code' => $this->order_obj->tracking_code,
                        'order_status' => $this->order_obj->status,
                        'admin_notes' => $this->order_obj->notes,
                        'product_ids' => $product_ids,
                        'quantities' => $quantities,
                        'prices_at_order' => $prices_at_order,
                        'custom_descriptions' => $custom_descriptions
                    ];


                    $validation_error_found = false;

                    if (empty($this->order_obj->customer_id)) {
                        $error_message = "لطفاً مشتری را انتخاب کنید.";
                        $validation_error_found = true;
                    } elseif (empty($this->order_obj->order_date_shamsi)) {
                        $error_message = "لطفاً تاریخ ثبت سفارش را وارد کنید.";
                        $validation_error_found = true;
                    } elseif ($this->order_obj->total_amount <= 0) {
                        $error_message = "مبلغ کل سفارش باید بیشتر از صفر باشد.";
                        $validation_error_found = true;
                    } elseif (empty($product_ids)) {
                        $error_message = "لطفاً حداقل یک محصول به سفارش اضافه کنید.";
                        $validation_error_found = true;
                    } else {
                        foreach ($product_ids as $key => $product_id) {
                            if (empty($product_id) || !isset($quantities[$key]) || !isset($prices_at_order[$key]) || (int)$quantities[$key] <= 0 || (float)$prices_at_order[$key] <= 0) {
                                $error_message = "اطلاعات محصول(های) سفارش نامعتبر است. لطفاً محصول، تعداد و قیمت را بررسی کنید.";
                                $validation_error_found = true;
                                break;
                            }
                        }
                    }

                    if (!$validation_error_found) {
                        if ($this->order_obj->create()) {
                            $order_id = $this->db->lastInsertId();

                            $all_items_created = true;
                            foreach ($product_ids as $key => $product_id) {
                                $this->order_item_obj->order_id = $order_id;
                                $this->order_item_obj->product_id = (int)$product_ids[$key];
                                $this->order_item_obj->quantity = (int)$quantities[$key];
                                $this->order_item_obj->price_at_order = (float)$prices_at_order[$key];
                                $this->order_item_obj->custom_description = trim($custom_descriptions[$key] ?? '');

                                if (!$this->order_item_obj->create()) {
                                    $all_items_created = false;
                                    break;
                                }
                            }

                            if ($all_items_created) {
                                $success_message = "سفارش با موفقیت ثبت شد.";
                                $should_redirect = true;
                            } else {
                                $this->order_obj->id = $order_id;
                                $this->order_obj->delete();
                                $error_message = "خطا در ثبت آیتم‌های سفارش. سفارش ثبت نشد.";
                            }
                        } else {
                            $error_message = "خطا در ثبت سفارش. لطفاً دوباره تلاش کنید. (خطای پایگاه داده)";
                        }
                    } else {
                        $open_add_modal = true;
                    }
                    break;

                case 'edit_order':
                    $order_id = $_POST['order_id'] ?? null;
                    if (!$order_id) {
                        $error_message = "شناسه سفارش برای ویرایش نامعتبر است.";
                        break;
                    }
                    $this->order_obj->id = $order_id;

                    $this->order_obj->customer_id = trim($_POST['customer_id'] ?? '');
                    $this->order_obj->order_date_shamsi = trim($_POST['order_date'] ?? '');
                    $this->order_obj->deposit_amount = trim($_POST['down_payment_raw'] ?? 0);
                    $this->order_obj->total_amount = trim($_POST['total_amount_raw'] ?? 0);
                    $this->order_obj->shipping_date = trim($_POST['estimated_delivery_date'] ?? '');
                    $this->order_obj->shipping_method = trim($_POST['shipping_method'] ?? '');
                    $this->order_obj->tracking_code = trim($_POST['tracking_code'] ?? '');
                    $this->order_obj->status = trim($_POST['order_status'] ?? 'processing'); // Default to 'processing'
                    $this->order_obj->notes = trim($_POST['admin_notes'] ?? '');

                    $product_ids = $_POST['product_ids'] ?? [];
                    $quantities = $_POST['quantities'] ?? [];
                    $prices_at_order = $_POST['prices_at_order'] ?? [];
                    $custom_descriptions = $_POST['custom_descriptions'] ?? [];

                    $form_data_edit = [
                        'order_id' => $this->order_obj->id,
                        'customer_id' => $this->order_obj->customer_id,
                        'order_date' => $this->order_obj->order_date_shamsi,
                        'down_payment_raw' => $this->order_obj->deposit_amount,
                        'total_amount_raw' => $this->order_obj->total_amount,
                        'estimated_delivery_date' => $this->order_obj->shipping_date,
                        'shipping_method' => $this->order_obj->shipping_method,
                        'tracking_code' => $this->order_obj->tracking_code,
                        'order_status' => $this->order_obj->status,
                        'admin_notes' => $this->order_obj->notes,
                        'product_ids' => $product_ids,
                        'quantities' => $quantities,
                        'prices_at_order' => $prices_at_order,
                        'custom_descriptions' => $custom_descriptions
                    ];

                    $validation_error_found = false;

                    if (empty($this->order_obj->customer_id)) {
                        $error_message = "لطفاً مشتری را انتخاب کنید.";
                        $validation_error_found = true;
                    } elseif (empty($this->order_obj->order_date_shamsi)) {
                        $error_message = "لطفاً تاریخ ثبت سفارش را وارد کنید.";
                        $validation_error_found = true;
                    } elseif ($this->order_obj->total_amount <= 0) {
                        $error_message = "مبلغ کل سفارش باید بیشتر از صفر باشد.";
                        $validation_error_found = true;
                    } elseif (empty($product_ids)) {
                        $error_message = "لطفاً حداقل یک محصول به سفارش اضافه کنید.";
                        $validation_error_found = true;
                    } else {
                        foreach ($product_ids as $key => $product_id) {
                            if (empty($product_id) || !isset($quantities[$key]) || !isset($prices_at_order[$key]) || (int)$quantities[$key] <= 0 || (float)$prices_at_order[$key] <= 0) {
                                $error_message = "اطلاعات محصول(های) سفارش نامعتبر است. لطفاً محصول، تعداد و قیمت را بررسی کنید.";
                                $validation_error_found = true;
                                break;
                            }
                        }
                    }

                    if (!$validation_error_found) {
                        if ($this->order_obj->update()) {
                            $this->order_item_obj->deleteByOrderId($order_id);

                            $all_items_created = true;
                            foreach ($product_ids as $key => $product_id) {
                                $this->order_item_obj->order_id = $order_id;
                                $this->order_item_obj->product_id = (int)$product_ids[$key];
                                $this->order_item_obj->quantity = (int)$quantities[$key];
                                $this->order_item_obj->price_at_order = (float)$prices_at_order[$key];
                                $this->order_item_obj->custom_description = trim($custom_descriptions[$key] ?? '');

                                if (!$this->order_item_obj->create()) {
                                    $all_items_created = false;
                                    break;
                                }
                            }

                            if ($all_items_created) {
                                $success_message = "سفارش با موفقیت ویرایش شد.";
                                $should_redirect = true;
                            } else {
                                $error_message = "خطا در ویرایش آیتم‌های سفارش. سفارش ویرایش نشد.";
                            }
                        } else {
                            $error_message = "خطا در ویرایش سفارش. لطفاً دوباره تلاش کنید. (خطای پایگاه داده)";
                        }
                    } else {
                        $open_edit_modal = true;
                    }
                    break;

                case 'delete_order':
                    $this->order_obj->id = $_POST['delete_order_id'];
                    if ($this->order_item_obj->deleteByOrderId($this->order_obj->id)) {
                        if ($this->order_obj->delete()) {
                            $success_message = "سفارش با موفقیت حذف شد.";
                            // [NEW] تنظیم مجدد صفحه برای حذف سفارش (مانند ProductsController)
                            $total_rows_after_delete = $this->order_obj->countAll($search_term, $customer_filter_id, $status_filter, $shipping_method_filter);
                            $records_per_page_check = 10;
                            $total_pages_after_delete = ceil($total_rows_after_delete / $records_per_page_check);

                            if ($page > $total_pages_after_delete && $total_pages_after_delete > 0 && $page > 1) {
                                $page--;
                            } elseif ($total_pages_after_delete === 0) {
                                $page = 1;
                            }
                            $should_redirect = true;
                        } else {
                            $error_message = "خطا در حذف سفارش اصلی.";
                        }
                    } else {
                        $error_message = "خطا در حذف آیتم‌های سفارش. سفارش حذف نشد.";
                    }
                    break;
            }

            if ($should_redirect) {
                $_SESSION['success_message'] = $success_message;
                $_SESSION['error_message'] = $error_message;
                unset($_SESSION['form_data_add']);
                unset($_SESSION['open_add_modal']);
                unset($_SESSION['form_data_edit']);
                unset($_SESSION['open_edit_modal']);

                // [NEW] ساخت URL ریدایرکت با حفظ پارامترهای فیلتر و صفحه‌بندی
                $redirect_url = "orders?page=" . $page;
                if (!empty($search_term)) $redirect_url .= "&search=" . urlencode($search_term);
                if ($customer_filter_id !== null) $redirect_url .= "&customer_filter=" . $customer_filter_id;
                if ($status_filter !== null) $redirect_url .= "&status_filter=" . urlencode($status_filter);
                if ($shipping_method_filter !== null) $redirect_url .= "&shipping_method_filter=" . urlencode($shipping_method_filter);
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

        // واکشی اطلاعات فرم ویرایش و وضعیت باز بودن مودال ویرایش از سشن
        if (isset($_SESSION['form_data_edit'])) {
            $order_to_edit = $_SESSION['form_data_edit'];
            $items_stmt = $this->order_item_obj->readByOrderId($order_to_edit['order_id']);
            if ($items_stmt) {
                $order_items_for_edit = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                $order_to_edit['order_items'] = $order_items_for_edit;
            } else {
                $order_to_edit['order_items'] = [];
            }

            unset($_SESSION['form_data_edit']);
            $open_edit_modal = true;
        }
        if (isset($_SESSION['open_edit_modal'])) {
            $open_edit_modal = true;
            unset($_SESSION['open_edit_modal']);
        }


        // بررسی برای حالت ویرایش مشتری (وقتی ID مشتری از طریق GET می‌آید، مثلاً پس از ریدایرکت)
        if ($order_to_edit === null && isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
            $order_id_to_edit = (int)$_GET['edit_id'];
            $this->order_obj->id = $order_id_to_edit;
            $order_details_from_db = $this->order_obj->readOne();

            if ($order_details_from_db) {
                $order_to_edit = $order_details_from_db;
                $items_stmt = $this->order_item_obj->readByOrderId($order_id_to_edit);
                if ($items_stmt) {
                    $order_to_edit['order_items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $order_to_edit['order_items'] = [];
                }
                $open_edit_modal = true;
            } else {
                $error_message = "سفارش مورد نظر برای ویرایش یافت نشد.";
            }
        }

        // [NEW] واکشی لیست کامل سفارشات با صفحه‌بندی و فیلتر
        $orders_stmt = $this->order_obj->readPaging($from_record_num, $records_per_page, $search_term, $customer_filter_id, $status_filter, $shipping_method_filter, $sort_by, $sort_order);
        if ($orders_stmt === false) {
            $error_message = "خطا در واکشی سفارشات از پایگاه داده.";
            $orders_array = [];
            $num_orders = 0; // [NEW] تعداد کل سفارشات
        } else {
            $orders_array = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
            $num_orders = $orders_stmt->rowCount(); // تعداد سفارشات واکشی شده در صفحه فعلی
        }

        // [NEW] محاسبه کل صفحات برای صفحه‌بندی
        $total_rows = $this->order_obj->countAll($search_term, $customer_filter_id, $status_filter, $shipping_method_filter);
        $total_pages = ceil($total_rows / $records_per_page);


        // واکشی لیست مشتریان برای دراپ‌داون فرم (نیاز به نام کامل)
        // [FIXED] Fetch all customers without pagination or filters to ensure all are available for dropdowns
        $customer_read_stmt = $this->customer_obj->readPaging(0, 99999, '', null, 'first_name', 'ASC'); // Increased limit to ensure all customers are fetched
        if ($customer_read_stmt === false) {
            $all_customers = [];
            error_log("Error fetching customers for order form: Customer readPaging returned false.");
        } else {
            $fetched_customers = $customer_read_stmt->fetchAll(PDO::FETCH_ASSOC);
            $all_customers = [];
            $seen_ids = []; // To track unique IDs

            // [FIXED] Ensure uniqueness by customer ID and prepare full_name_display
            foreach ($fetched_customers as $customer) {
                if (!isset($seen_ids[$customer['id']])) { // Add only if ID has not been seen
                    $fullName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
                    if (empty($fullName)) {
                        $fullName = $customer['phone'] ?? 'نامشخص'; // Fallback to phone if name is empty
                    }
                    $customer['full_name_display'] = $fullName . ' (ID: ' . $customer['id'] . ')';
                    $all_customers[] = $customer;
                    $seen_ids[$customer['id']] = true;
                }
            }
            // Debugging: Log the final all_customers array before passing to view
            error_log("Final all_customers array for orders dropdown: " . print_r($all_customers, true));
        }

        // واکشی لیست محصولات برای دراپ‌داون فرم (نیاز به نام و قیمت)
        $all_products_stmt = $this->product_obj->readPaging(0, 1000, '', null, 1); // فقط محصولات فعال
        if ($all_products_stmt === false) {
            $all_products = [];
        } else {
            $all_products = $all_products_stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // [اصلاح شده] لیست وضعیت‌های سفارش برای دراپ‌داون فیلتر
        $order_statuses = ['processing', 'shipped', 'cancelled'];

        // [NEW] لیست روش‌های ارسال برای دراپ‌داون فیلتر
        $shipping_methods = ['post', 'chapart', 'tipax', 'delivery_company', 'pickup'];


        // استخراج متغیرها برای در دسترس قرار گرفتن در نمای شامل شده
        extract(compact('pageTitle', 'success_message', 'error_message', 'orders_array',
                        'all_customers', 'all_products', 'form_data_add', 'open_add_modal',
                        'order_to_edit', 'open_edit_modal',
                        'search_term', 'customer_filter_id', 'status_filter', 'shipping_method_filter',
                        'sort_by', 'sort_order', 'page', 'records_per_page', 'total_rows', 'total_pages',
                        'order_statuses', 'shipping_methods' // [NEW] فیلترهای جدید
                    ));

        // شامل کردن فایل هدر، نمای اصلی و فوتر
        include __DIR__ . '/../views/includes/header.php';
        include __DIR__ . '/../views/orders/index.php';
        include __DIR__ . '/../views/includes/footer.php';
    }

    // متد مشاهده سفارش (برای نمایش جزئیات یک سفارش خاص)
    public function view() {
        $order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $order_details = null;
        $order_items = [];
        $error_message = '';

        if ($order_id > 0) {
            $this->order_obj->id = $order_id;
            $order_details = $this->order_obj->readOne();

            if ($order_details) {
                $items_stmt = $this->order_item_obj->readByOrderId($order_id);
                if ($items_stmt) {
                    $order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $error_message = "خطا در واکشی آیتم‌های سفارش.";
                }
            } else {
                $error_message = "سفارش مورد نظر یافت نشد.";
            }
        } else {
            $error_message = "شناسه سفارش نامعتبر است.";
        }

        header('Content-Type: application/json');
        echo json_encode(['order_details' => $order_details, 'order_items' => $order_items, 'error' => !empty($error_message) ? $error_message : null]);
        exit();
    }
}
