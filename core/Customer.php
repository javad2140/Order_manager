<?php
// core/Customer.php

class Customer {
    private $conn;
    private $table_name = "customers"; // مطمئن شوید نام جدول صحیح است

    // Object properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $instagram_id; // اضافه شدن ویژگی instagram_id
    public $phone;
    public $postal_code;
    public $state;
    public $city;
    public $address_detail;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // متد برای بررسی وجود ایمیل
    public function emailExists($email_to_check, $current_customer_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        if ($current_customer_id !== null) {
            $query .= " AND id != :current_customer_id";
        }
        $query .= " LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email_to_check);
        if ($current_customer_id !== null) {
            $stmt->bindParam(':current_customer_id', $current_customer_id, PDO::PARAM_INT);
        }
        try {
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Customer emailExists error: " . $e->getMessage() . " Query: " . $query);
            return false;
        }
    }

    // متد برای بررسی وجود Instagram ID
    public function instagramIdExists($instagram_id_to_check, $current_customer_id = null) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE instagram_id = :instagram_id";
        if ($current_customer_id !== null) {
            $query .= " AND id != :current_customer_id";
        }
        $query .= " LIMIT 0,1"; 
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':instagram_id', $instagram_id_to_check);
        if ($current_customer_id !== null) {
            $stmt->bindParam(':current_customer_id', $current_customer_id, PDO::PARAM_INT);
        }
        try {
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Customer instagramIdExists error: " . $e->getMessage() . " Query: " . $query);
            return false;
        }
    }


    // متد برای افزودن مشتری جدید
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                    SET first_name=:first_name, last_name=:last_name, email=:email, instagram_id=:instagram_id,
                        phone=:phone, postal_code=:postal_code, state=:state,
                        city=:city, address_detail=:address_detail";

        $stmt = $this->conn->prepare($query);

        // پاکسازی و آماده‌سازی ورودی‌ها
        // مقادیر خالی را به NULL تبدیل می‌کنیم تا با محدودیت های NOT NULL در دیتابیس سازگار باشد.
        $first_name_val = empty($this->first_name) ? null : htmlspecialchars(strip_tags($this->first_name));
        $first_name_param_type = ($first_name_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $last_name_val = empty($this->last_name) ? null : htmlspecialchars(strip_tags($this->last_name));
        $last_name_param_type = ($last_name_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $email_val = empty($this->email) ? null : htmlspecialchars(strip_tags($this->email));
        $email_param_type = ($email_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;
        
        $instagram_id_val = empty($this->instagram_id) ? null : htmlspecialchars(strip_tags($this->instagram_id));
        $instagram_id_param_type = ($instagram_id_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $phone_val = empty($this->phone) ? null : htmlspecialchars(strip_tags($this->phone));
        $phone_param_type = ($phone_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $postal_code_val = empty($this->postal_code) ? null : htmlspecialchars(strip_tags($this->postal_code));
        $postal_code_param_type = ($postal_code_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $state_val = empty($this->state) ? null : htmlspecialchars(strip_tags($this->state));
        $state_param_type = ($state_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $city_val = empty($this->city) ? null : htmlspecialchars(strip_tags($this->city));
        $city_param_type = ($city_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $address_detail_val = empty($this->address_detail) ? null : htmlspecialchars(strip_tags($this->address_detail));
        $address_detail_param_type = ($address_detail_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        // اتصال مقادیر به کوئری
        $stmt->bindParam(":first_name", $first_name_val, $first_name_param_type);
        $stmt->bindParam(":last_name", $last_name_val, $last_name_param_type);
        $stmt->bindParam(":email", $email_val, $email_param_type);
        $stmt->bindParam(":instagram_id", $instagram_id_val, $instagram_id_param_type);
        $stmt->bindParam(":phone", $phone_val, $phone_param_type);
        $stmt->bindParam(":postal_code", $postal_code_val, $postal_code_param_type);
        $stmt->bindParam(":state", $state_val, $state_param_type);
        $stmt->bindParam(":city", $city_val, $city_param_type);
        $stmt->bindParam(":address_detail", $address_detail_val, $address_detail_param_type);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Customer creation error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // [NEW] متد برای خواندن همه مشتریان با pagination و optional filters
    public function readPaging($from_record_num, $records_per_page, $search_term = '', $state_filter = null, $sort_by = 'created_at', $sort_order = 'DESC') {
        $query = "SELECT id, first_name, last_name, email, instagram_id, phone, postal_code, state, city, address_detail, created_at, updated_at
                  FROM " . $this->table_name;
        
        $conditions = [];
        $params_for_execute = [];

        if (!empty($search_term)) {
            $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR instagram_id LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if (!empty($state_filter)) {
            $conditions[] = "state = ?";
            $params_for_execute[] = $state_filter;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $valid_sort_columns = ['id', 'first_name', 'last_name', 'email', 'phone', 'created_at']; 
        $sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';
        $sort_order = (strtoupper($sort_order) === 'ASC' || strtoupper($sort_order) === 'DESC') ? $sort_order : 'DESC';
        $query .= " ORDER BY " . $sort_by . " " . $sort_order;

        $query .= " LIMIT ?, ?";
        $params_for_execute[] = $from_record_num;
        $params_for_execute[] = $records_per_page;

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute($params_for_execute);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Customer readPaging error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return false;
        }
    }

    // [NEW] متد برای شمارش کل مشتریان با optional filters
    public function countAll($search_term = '', $state_filter = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name;
        
        $conditions = [];
        $params_for_execute = [];

        if (!empty($search_term)) {
            $conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? OR instagram_id LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if (!empty($state_filter)) {
            $conditions[] = "state = ?";
            $params_for_execute[] = $state_filter;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute($params_for_execute);
            $row = $stmt->fetch(PDO::FETCH_NUM);
            return $row[0];
        } catch (PDOException $e) {
            error_log("Customer countAll error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return 0;
        }
    }

    /**
     * [NEW METHOD] Retrieves the count of new customers for a given month and year (Shamsi).
     * This method assumes a `created_at` column of type TIMESTAMP or DATETIME.
     * It also assumes jdf.php is available to convert Shamsi to Gregorian for the query.
     *
     * @param int $shamsi_year The Shamsi year.
     * @param int $shamsi_month The Shamsi month.
     * @return int The count of new customers.
     */
    public function getCustomerCountByMonthYear($shamsi_year, $shamsi_month) {
        // Convert Shamsi month/year to Gregorian start and end dates
        // Note: jdf function `jalali_to_gregorian` returns an array [y, m, d]
        $gregorian_start_array = jalali_to_gregorian($shamsi_year, $shamsi_month, 1);
        $start_date = implode('-', $gregorian_start_array) . " 00:00:00";

        // To get the end date, we find the start of the next month and go back one second.
        $next_month = $shamsi_month + 1;
        $next_year = $shamsi_year;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }
        $gregorian_end_array = jalali_to_gregorian($next_year, $next_month, 1);
        // Create a DateTime object for the start of the next month to safely subtract one second
        $end_of_month_obj = new DateTime(implode('-', $gregorian_end_array));
        $end_of_month_obj->modify('-1 second');
        $end_date = $end_of_month_obj->format('Y-m-d H:i:s');


        $query = "SELECT COUNT(id) AS customer_count 
                  FROM " . $this->table_name . " 
                  WHERE created_at >= :start_date AND created_at <= :end_date";
        
        $stmt = $this->conn->prepare($query);
        $params = [
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ];

        try {
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['customer_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting customer count by month/year: " . $e->getMessage());
            return 0;
        }
    }

    // متد برای خواندن یک مشتری بر اساس ID
    public function readOne() {
        $query = "SELECT id, first_name, last_name, email, instagram_id, phone, postal_code, state, city, address_detail, created_at, updated_at
                  FROM " . $this->table_name . "
                  WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        } catch (PDOException $e) {
            error_log("Customer readOne error: " . $e->getMessage());
            return false;
        }
    }

    // متد برای به‌روزرسانی مشتری موجود
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                    SET first_name=:first_name, last_name=:last_name, email=:email, instagram_id=:instagram_id,
                        phone=:phone, postal_code=:postal_code, state=:state,
                        city=:city, address_detail=:address_detail, updated_at=CURRENT_TIMESTAMP
                    WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // پاکسازی و آماده‌سازی ورودی‌ها
        $first_name_val = empty($this->first_name) ? null : htmlspecialchars(strip_tags($this->first_name));
        $first_name_param_type = ($first_name_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $last_name_val = empty($this->last_name) ? null : htmlspecialchars(strip_tags($this->last_name));
        $last_name_param_type = ($last_name_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $email_val = empty($this->email) ? null : htmlspecialchars(strip_tags($this->email));
        $email_param_type = ($email_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;
        
        $instagram_id_val = empty($this->instagram_id) ? null : htmlspecialchars(strip_tags($this->instagram_id));
        $instagram_id_param_type = ($instagram_id_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $phone_val = empty($this->phone) ? null : htmlspecialchars(strip_tags($this->phone));
        $phone_param_type = ($phone_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $postal_code_val = empty($this->postal_code) ? null : htmlspecialchars(strip_tags($this->postal_code));
        $postal_code_param_type = ($postal_code_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $state_val = empty($this->state) ? null : htmlspecialchars(strip_tags($this->state));
        $state_param_type = ($state_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $city_val = empty($this->city) ? null : htmlspecialchars(strip_tags($this->city));
        $city_param_type = ($city_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $address_detail_val = empty($this->address_detail) ? null : htmlspecialchars(strip_tags($this->address_detail));
        $address_detail_param_type = ($address_detail_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        // اتصال مقادیر
        $stmt->bindParam(":first_name", $first_name_val, $first_name_param_type);
        $stmt->bindParam(":last_name", $last_name_val, $last_name_param_type);
        $stmt->bindParam(":email", $email_val, $email_param_type);
        $stmt->bindParam(":instagram_id", $instagram_id_val, $instagram_id_param_type);
        $stmt->bindParam(":phone", $phone_val, $phone_param_type);
        $stmt->bindParam(":postal_code", $postal_code_val, $postal_code_param_type);
        $stmt->bindParam(":state", $state_val, $state_param_type);
        $stmt->bindParam(":city", $city_val, $city_param_type);
        $stmt->bindParam(":address_detail", $address_detail_val, $address_detail_param_type);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Customer update error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Customer deletion error: " . $e->getMessage());
            return false;
        }
        return false;
    }
}
