<?php

class Order {
    private $conn;
    private $table_name = "orders";

    public $id;
    public $customer_id;
    public $order_date_shamsi;
    public $deposit_amount;
    public $total_amount;
    public $shipping_date;
    public $shipping_method;
    public $tracking_code;
    public $status;
    public $notes;
    public $created_at;
    public $updated_at;

    public function __construct($db) { // Added $db parameter
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                    SET customer_id=:customer_id, order_date_shamsi=:order_date_shamsi, 
                        deposit_amount=:deposit_amount, total_amount=:total_amount, 
                        shipping_date=:shipping_date, shipping_method=:shipping_method, 
                        tracking_code=:tracking_code, status=:status, notes=:notes";

        $stmt = $this->conn->prepare($query);

        $this->order_date_shamsi = htmlspecialchars(strip_tags($this->order_date_shamsi));
        $this->shipping_method = htmlspecialchars(strip_tags($this->shipping_method));
        $this->tracking_code = htmlspecialchars(strip_tags($this->tracking_code));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $this->deposit_amount = filter_var($this->deposit_amount, FILTER_VALIDATE_FLOAT);
        $this->total_amount = filter_var($this->total_amount, FILTER_VALIDATE_FLOAT);

        $shipping_date_val = empty($this->shipping_date) ? null : $this->shipping_date;
        $shipping_method_val = empty($this->shipping_method) ? null : $this->shipping_method;
        $tracking_code_val = empty($this->tracking_code) ? null : $this->tracking_code;
        $notes_val = empty($this->notes) ? null : $this->notes;
        $customer_id_val = empty($this->customer_id) ? null : (int)$this->customer_id;


        $stmt->bindParam(":customer_id", $customer_id_val, PDO::PARAM_INT);
        $stmt->bindParam(":order_date_shamsi", $this->order_date_shamsi);
        $stmt->bindParam(":deposit_amount", $this->deposit_amount);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":shipping_date", $shipping_date_val, PDO::PARAM_STR); 
        $stmt->bindParam(":shipping_method", $shipping_method_val, PDO::PARAM_STR);
        $stmt->bindParam(":tracking_code", $tracking_code_val, PDO::PARAM_STR);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $notes_val, PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Order creation error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    public function readPaging($from_record_num, $records_per_page, $search_term = '', $customer_filter_id = null, $status_filter = null, $shipping_method_filter = null, $sort_by = 'created_at', $sort_order = 'DESC') {
        $query = "SELECT o.id, o.order_date_shamsi, o.deposit_amount, o.total_amount, o.shipping_date, 
                         o.shipping_method, o.tracking_code, o.status, o.notes, o.created_at, o.updated_at,
                         c.first_name, c.last_name, o.customer_id,
                         GROUP_CONCAT(p.name ORDER BY p.name SEPARATOR ', ') AS product_names
                  FROM " . $this->table_name . " o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  LEFT JOIN products p ON oi.product_id = p.id";
        
        $conditions = [];
        $params_for_execute = []; 

        if (!empty($search_term)) {
            $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR p.name LIKE ? OR p.sku LIKE ? OR o.notes LIKE ? OR o.tracking_code LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if ($customer_filter_id !== null && $customer_filter_id !== '') {
            $conditions[] = "o.customer_id = ?";
            $params_for_execute[] = $customer_filter_id;
        }

        if (!empty($status_filter)) {
            $conditions[] = "o.status = ?";
            $params_for_execute[] = $status_filter;
        }

        if (!empty($shipping_method_filter)) {
            $conditions[] = "o.shipping_method = ?";
            $params_for_execute[] = $shipping_method_filter;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $query .= " GROUP BY o.id"; // Group by order ID to get unique orders and concatenated product names

        $valid_sort_columns = ['id', 'order_date_shamsi', 'total_amount', 'status', 'created_at']; 
        $sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';
        $sort_order = (strtoupper($sort_order) === 'ASC' || strtoupper($sort_order) === 'DESC') ? $sort_order : 'DESC';
        $query .= " ORDER BY o." . $sort_by . " " . $sort_order;

        $query .= " LIMIT ?, ?";
        $params_for_execute[] = $from_record_num;
        $params_for_execute[] = $records_per_page;

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute($params_for_execute);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Order readPaging error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return false; 
        }
    }

    public function countAll($search_term = '', $customer_filter_id = null, $status_filter = null, $shipping_method_filter = null) {
        $query = "SELECT COUNT(DISTINCT o.id) FROM " . $this->table_name . " o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  LEFT JOIN order_items oi ON o.id = oi.order_id
                  LEFT JOIN products p ON oi.product_id = p.id";
        
        $conditions = [];
        $params_for_execute = []; 

        if (!empty($search_term)) {
            $conditions[] = "(c.first_name LIKE ? OR c.last_name LIKE ? OR p.name LIKE ? OR p.sku LIKE ? OR o.notes LIKE ? OR o.tracking_code LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if ($customer_filter_id !== null && $customer_filter_id !== '') {
            $conditions[] = "o.customer_id = ?";
            $params_for_execute[] = $customer_filter_id;
        }

        if (!empty($status_filter)) {
            $conditions[] = "o.status = ?";
            $params_for_execute[] = $status_filter;
        }

        if (!empty($shipping_method_filter)) {
            $conditions[] = "o.shipping_method = ?";
            $params_for_execute[] = $shipping_method_filter;
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
            error_log("Order countAll error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return 0;
        }
    }

    public function readOne() {
        $query = "SELECT o.id, o.customer_id, o.order_date_shamsi, o.deposit_amount, o.total_amount, 
                         o.shipping_date, o.shipping_method, o.tracking_code, o.status, o.notes, 
                         o.created_at, o.updated_at,
                         c.first_name, c.last_name
                  FROM " . $this->table_name . " o
                  LEFT JOIN customers c ON o.customer_id = c.id
                  WHERE o.id = :id"; 

        $query .= " LIMIT 0,1"; 

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC); 
            return $row; 
        } catch (PDOException $e) {
            error_log("Order readOne error: " . $e->getMessage() . " Query: " . $query);
            return false;
        }
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                    SET customer_id=:customer_id, order_date_shamsi=:order_date_shamsi, 
                        deposit_amount=:deposit_amount, total_amount=:total_amount, 
                        shipping_date=:shipping_date, shipping_method=:shipping_method, 
                        tracking_code=:tracking_code, status=:status, notes=:notes, updated_at=CURRENT_TIMESTAMP 
                    WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->order_date_shamsi = htmlspecialchars(strip_tags($this->order_date_shamsi));
        $this->shipping_method = htmlspecialchars(strip_tags($this->shipping_method));
        $this->tracking_code = htmlspecialchars(strip_tags($this->tracking_code));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $this->deposit_amount = filter_var($this->deposit_amount, FILTER_VALIDATE_FLOAT);
        $this->total_amount = filter_var($this->total_amount, FILTER_VALIDATE_FLOAT);

        $shipping_date_val = empty($this->shipping_date) ? null : $this->shipping_date;
        $shipping_method_val = empty($this->shipping_method) ? null : $this->shipping_method;
        $tracking_code_val = empty($this->tracking_code) ? null : $this->tracking_code;
        $notes_val = empty($this->notes) ? null : $this->notes;
        $customer_id_val = empty($this->customer_id) ? null : (int)$this->customer_id;

        $stmt->bindParam(":customer_id", $customer_id_val, PDO::PARAM_INT);
        $stmt->bindParam(":order_date_shamsi", $this->order_date_shamsi);
        $stmt->bindParam(":deposit_amount", $this->deposit_amount);
        $stmt->bindParam(":total_amount", $this->total_amount);
        $stmt->bindParam(":shipping_date", $shipping_date_val, PDO::PARAM_STR);
        $stmt->bindParam(":shipping_method", $shipping_method_val, PDO::PARAM_STR);
        $stmt->bindParam(":tracking_code", $tracking_code_val, PDO::PARAM_STR);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":notes", $notes_val, PDO::PARAM_STR);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Order update error: " . $e->getMessage());
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
            error_log("Order deletion error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    public function getTotalAmountByMonthYear($shamsi_year, $shamsi_month, $status = null) {
        $query = "SELECT SUM(total_amount) AS total_sum FROM " . $this->table_name . " WHERE SUBSTRING(order_date_shamsi, 1, 4) = :year AND SUBSTRING(order_date_shamsi, 6, 2) = :month AND status != 'cancelled'";
        $params = [':year' => $shamsi_year, ':month' => sprintf('%02d', $shamsi_month)];

        if ($status !== null) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['total_sum'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting total amount by month/year: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalAmountByYear($shamsi_year, $status = null) {
        $query = "SELECT SUM(total_amount) AS total_sum FROM " . $this->table_name . " WHERE SUBSTRING(order_date_shamsi, 1, 4) = :year AND status != 'cancelled'";
        $params = [':year' => $shamsi_year];

        if ($status !== null) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['total_sum'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting total amount by year: " . $e->getMessage());
            return 0;
        }
    }

    public function getOrderCountByMonthYear($shamsi_year, $shamsi_month, $status = null) {
        $query = "SELECT COUNT(id) AS order_count FROM " . $this->table_name . " WHERE SUBSTRING(order_date_shamsi, 1, 4) = :year AND SUBSTRING(order_date_shamsi, 6, 2) = :month";
        $params = [':year' => $shamsi_year, ':month' => sprintf('%02d', $shamsi_month)];

        if ($status !== null) {
            if (is_array($status)) {
                if (count($status) > 0) {
                    $placeholders = [];
                    foreach ($status as $key => $value) {
                        $param_name = ':status_' . $key;
                        $placeholders[] = $param_name;
                        $params[$param_name] = $value;
                    }
                    $query .= " AND status IN (" . implode(', ', $placeholders) . ")";
                }
            } else {
                $query .= " AND status = :status";
                $params[':status'] = $status;
            }
        }

        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($row['order_count'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting order count by month/year: " . $e->getMessage());
            return 0;
        }
    }

    public function getAverageOrderAmountByMonthYear($shamsi_year, $shamsi_month, $status = null) {
        $query = "SELECT AVG(total_amount) AS avg_amount FROM " . $this->table_name . " WHERE SUBSTRING(order_date_shamsi, 1, 4) = :year AND SUBSTRING(order_date_shamsi, 6, 2) = :month AND status != 'cancelled'";
        $params = [':year' => $shamsi_year, ':month' => sprintf('%02d', $shamsi_month)];

        if ($status !== null) {
            $query .= " AND status = :status";
            $params[':status'] = $status;
        }

        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute($params);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)($row['avg_amount'] ?? 0);
        } catch (PDOException $e) {
            error_log("Error getting average order amount by month/year: " . $e->getMessage());
            return 0;
        }
    }
}
