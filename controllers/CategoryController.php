<?php
// controllers/CategoryController.php

require_once __DIR__ . '/../core/Category.php';
require_once __DIR__ . '/../core/Database.php';

class CategoryController {
    private $db;
    private $category_obj;

    public function __construct($db) {
        $this->db = $db;
        $this->category_obj = new Category($this->db);
    }

    public function index() {
        // استفاده از سشن برای نمایش پیام‌ها پس از ریدایرکت
        $success_message = $_SESSION['success_message'] ?? '';
        $error_message = $_SESSION['error_message'] ?? '';
        unset($_SESSION['success_message'], $_SESSION['error_message']); // پاک کردن پیام از سشن

        // مدیریت ارسال فرم‌ها (افزودن، ویرایش، حذف)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_category':
                    $this->category_obj->name = trim($_POST['category_name']);
                    $this->category_obj->description = trim($_POST['category_description'] ?? '');
                    $this->category_obj->parent_id = empty($_POST['parent_category_id']) ? null : $_POST['parent_category_id'];

                    if ($this->category_obj->create()) {
                        $_SESSION['success_message'] = "دسته‌بندی با موفقیت اضافه شد.";
                    } else {
                        $_SESSION['error_message'] = "خطا در اضافه کردن دسته‌بندی. لطفا دوباره تلاش کنید.";
                    }
                    break;

                case 'edit_category':
                    $this->category_obj->id = $_POST['category_id'];
                    $this->category_obj->name = trim($_POST['edit_category_name']);
                    $this->category_obj->description = trim($_POST['edit_category_description'] ?? '');
                    $this->category_obj->parent_id = empty($_POST['edit_parent_category_id']) ? null : $_POST['edit_parent_category_id'];

                    if ($this->category_obj->parent_id !== null && $this->category_obj->parent_id == $this->category_obj->id) {
                        $_SESSION['error_message'] = "یک دسته‌بندی نمی‌تواند والد خودش باشد.";
                    } else {
                        if ($this->category_obj->update()) {
                            $_SESSION['success_message'] = "دسته‌بندی با موفقیت ویرایش شد.";
                        } else {
                            $_SESSION['error_message'] = "خطا در ویرایش دسته‌بندی. لطفا دوباره تلاش کنید.";
                        }
                    }
                    break;

                case 'delete_category':
                    $this->category_obj->id = $_POST['delete_category_id'];

                    // بررسی وجود محصولات و زیردسته‌ها قبل از حذف
                    if ($this->category_obj->hasSubcategories($this->category_obj->id)) {
                        $_SESSION['error_message'] = "این دسته‌بندی شامل زیردسته‌هایی است. ابتدا زیردسته‌ها را حذف یا منتقل کنید.";
                    } elseif ($this->category_obj->hasProducts($this->category_obj->id)) {
                        $_SESSION['error_message'] = "نمی‌توان این دسته‌بندی را حذف کرد زیرا محصولاتی به آن اختصاص داده شده‌اند.";
                    } else {
                        if ($this->category_obj->delete()) {
                            $_SESSION['success_message'] = "دسته‌بندی با موفقیت حذف شد.";
                        } else {
                            $_SESSION['error_message'] = "خطا در حذف دسته‌بندی. لطفا دوباره تلاش کنید.";
                        }
                    }
                    break;
            }
            // پس از هر عملیات POST، به مسیر دسته‌بندی‌ها هدایت شوید
            header("Location: " . BASE_URL . "categories");
            exit();
        }

        // واکشی همه دسته‌بندی‌ها به صورت سلسله‌مراتبی (درختی)
        $categories_hierarchical = $this->category_obj->getAllCategoriesHierarchical();

        $pageTitle = "مدیریت دسته‌بندی‌ها";
        $category_obj = $this->category_obj;

        extract(compact('pageTitle', 'success_message', 'error_message', 'categories_hierarchical', 'category_obj'));

        // [اصلاح شده] فراخوانی هدر از اینجا حذف شد چون در فایل view اصلی فراخوانی می‌شود
        include __DIR__ . '/../views/categories/index.php';
        require_once __DIR__ . '/../views/includes/footer.php';
    }
}
