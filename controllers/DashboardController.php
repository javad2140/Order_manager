<?php
// controllers/DashboardController.php

// نیاز به شامل کردن مدل Database و هر مدل دیگری که در داشبورد استفاده شود
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php'; 
require_once __DIR__ . '/../core/Order.php';    // اضافه شده
require_once __DIR__ . '/../core/Product.php';  // اضافه شده
require_once __DIR__ . '/../core/Customer.php'; // اضافه شده
require_once __DIR__ . '/../jdf.php';            // برای توابع تاریخ شمسی

class DashboardController {
    private $db;
    private $order_obj;
    private $product_obj;
    private $customer_obj;

    public function __construct($db) {
        $this->db = $db;
        // اینجا می‌توانید مدل‌های مورد نیاز را مقداردهی اولیه کنید
        $this->order_obj = new Order($this->db);
        $this->product_obj = new Product($this->db);
        $this->customer_obj = new Customer($this->db);
    }

    public function index() {
        $pageTitle = "داشبورد"; // عنوان صفحه
        $success_message = ""; // پیام موفقیت (در صورت نیاز)
        $error_message = "";   // پیام خطا (در صورت نیاز)

        // دریافت تاریخ شمسی فعلی
        $current_shamsi_date = jdate('Y/m/d', '', '', 'Asia/Tehran', 'en');
        list($current_shamsi_year, $current_shamsi_month, $current_shamsi_day) = explode('/', $current_shamsi_date);

        // مدیریت ناوبری ماهانه از طریق پارامترهای GET
        $display_shamsi_year = isset($_GET['year']) ? (int)$_GET['year'] : (int)$current_shamsi_year;
        $display_shamsi_month = isset($_GET['month']) ? (int)$_GET['month'] : (int)$current_shamsi_month;

        // تنظیم ماه و سال برای نمایش (اگر از ناوبری استفاده نشده باشد، ماه و سال جاری شمسی)
        // اطمینان از صحت ماه (بین 1 تا 12)
        if ($display_shamsi_month < 1) {
            $display_shamsi_month = 12;
            $display_shamsi_year--;
        } elseif ($display_shamsi_month > 12) {
            $display_shamsi_month = 1;
            $display_shamsi_year++;
        }

        // نام ماه‌های شمسی برای نمایش
        $shamsi_months = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر',
            5 => 'مرداد', 6 => 'شهریور', 7 => 'مهر', 8 => 'آبان',
            9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند'
        ];
        $display_month_name = $shamsi_months[$display_shamsi_month];

        // --- واکشی آمار ---

        // آمار ماهانه
        $total_orders_this_month = $this->order_obj->getOrderCountByMonthYear($display_shamsi_year, $display_shamsi_month);
        // [اصلاح شده] فقط وضعیت 'shipped' شمارش می‌شود و نام متغیر تغییر کرد
        $total_shipped_orders_this_month = $this->order_obj->getOrderCountByMonthYear($display_shamsi_year, $display_shamsi_month, ['shipped']);
        $total_amount_this_month = $this->order_obj->getTotalAmountByMonthYear($display_shamsi_year, $display_shamsi_month);
        $total_customers_this_month = $this->customer_obj->getCustomerCountByMonthYear($display_shamsi_year, $display_shamsi_month);

        // آمار سالانه
        $total_amount_this_year = $this->order_obj->getTotalAmountByYear($display_shamsi_year);

        // آمار کلی (همه زمان‌ها)
        $total_orders_all_time = $this->order_obj->countAll();
        $total_products_all_time = $this->product_obj->countAll();
        $total_customers_all_time = $this->customer_obj->countAll();

        // فعالیت‌های اخیر
        $latest_order_stmt = $this->order_obj->readPaging(0, 1, '', null, null, null, 'created_at', 'DESC');
        $latest_order = $latest_order_stmt ? $latest_order_stmt->fetch(PDO::FETCH_ASSOC) : null;
        $latest_product_stmt = $this->product_obj->readPaging(0, 1, '', null, null, 'created_at', 'DESC');
        $latest_product = $latest_product_stmt ? $latest_product_stmt->fetch(PDO::FETCH_ASSOC) : null;
        $latest_customer_stmt = $this->customer_obj->readPaging(0, 1, '', null, 'created_at', 'DESC');
        $latest_customer = $latest_customer_stmt ? $latest_customer_stmt->fetch(PDO::FETCH_ASSOC) : null;


        // آماده‌سازی متغیرها برای نما
        extract(compact(
            'pageTitle', 'success_message', 'error_message',
            'display_shamsi_year', 'display_shamsi_month', 'display_month_name',
            'total_orders_this_month', 'total_shipped_orders_this_month', // [اصلاح شده] نام متغیر به‌روز شد
            'total_amount_this_month', 'total_customers_this_month',
            'total_amount_this_year',
            'total_orders_all_time', 'total_products_all_time', 'total_customers_all_time',
            'latest_order', 'latest_product', 'latest_customer'
        ));

        // شامل کردن فایل هدر
        include __DIR__ . '/../views/includes/header.php';
        // شامل کردن فایل نمای داشبورد
        include __DIR__ . '/../views/dashboard/index.php';
        // شامل کردن فایل فوتر
        include __DIR__ . '/../views/includes/footer.php';
    }
}
