<?php
// controllers/BackupController.php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Backup.php';
// فایل کانفیگ را برای دسترسی به ثابت‌های DB فراخوانی می‌کنیم
require_once __DIR__ . '/../config/database.php'; 

class BackupController {

    /**
     * نمایش صفحه اصلی پشتیبان‌گیری
     */
    public function index($success_message = '', $error_message = '') {
        $pageTitle = "پشتیبان‌گیری و بازیابی";
        
        $view_data = [
            'pageTitle' => $pageTitle,
            'success_message' => $success_message,
            'error_message' => $error_message
        ];
        extract($view_data);

        include __DIR__ . '/../views/includes/header.php';
        include __DIR__ . '/../views/backup/index.php';
        include __DIR__ . '/../views/includes/footer.php';
    }

    /**
     * ایجاد و دانلود فایل پشتیبان
     */
    public function create() {
        $database = new Database();
        $db_conn = $database->getConnection();
        
        // [اصلاح شده] به جای کلاس ناموجود، از ثابت‌های تعریف شده استفاده می‌کنیم
        $backup = new Backup($db_conn, DB_HOST, DB_NAME, DB_USER, DB_PASS);
        $backup->createBackup();
    }

    /**
     * بازیابی اطلاعات از فایل پشتیبان
     */
    public function restore() {
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['backup_file']['tmp_name'];
            $file_name = $_FILES['backup_file']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_ext !== 'sql') {
                $this->index('', 'خطا: لطفاً فقط یک فایل با پسوند .sql بارگذاری کنید.');
                return;
            }

            $database = new Database();
            $db_conn = $database->getConnection();
            
            // [اصلاح شده] به جای کلاس ناموجود، از ثابت‌های تعریف شده استفاده می‌کنیم
            $backup = new Backup($db_conn, DB_HOST, DB_NAME, DB_USER, DB_PASS);
            
            if ($backup->restoreBackup($file_tmp_path)) {
                $this->index('پایگاه داده با موفقیت از فایل پشتیبان بازیابی شد.');
            } else {
                $this->index('', 'خطا در هنگام بازیابی پایگاه داده. لطفاً لاگ‌های سرور را بررسی کنید.');
            }

        } else {
            $this->index('', 'خطا در بارگذاری فایل. لطفاً دوباره تلاش کنید.');
        }
    }
}
