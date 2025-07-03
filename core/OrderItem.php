<?php

class OrderItem {
    private $conn;
    private $table_name = "order_items";

    public $order_id;
    public $product_id;
    public $quantity;
    public $price_at_order;
    public $custom_description; // [NEW] افزودن ویژگی برای توضیحات سفارشی محصول در سفارش
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET order_id=:order_id, product_id=:product_id, 
                      quantity=:quantity, price_at_order=:price_at_order, 
                      custom_description=:custom_description"; // [MODIFIED] اضافه کردن custom_description به کوئری

        $stmt = $this->conn->prepare($query);

        $this->order_id = (int)$this->order_id;
        $this->product_id = (int)$this->product_id;
        $this->quantity = (int)$this->quantity;
        $this->price_at_order = filter_var($this->price_at_order, FILTER_VALIDATE_FLOAT);
        $this->custom_description = htmlspecialchars(strip_tags($this->custom_description)); // [NEW] پاکسازی توضیحات

        // [MODIFIED] Bind کردن custom_description (می‌تواند NULL باشد)
        $custom_description_val = empty($this->custom_description) ? null : $this->custom_description;
        $custom_description_param_type = ($custom_description_val === null) ? PDO::PARAM_NULL : PDO::PARAM_STR;

        $stmt->bindParam(":order_id", $this->order_id, PDO::PARAM_INT);
        $stmt->bindParam(":product_id", $this->product_id, PDO::PARAM_INT);
        $stmt->bindParam(":quantity", $this->quantity, PDO::PARAM_INT);
        $stmt->bindParam(":price_at_order", $this->price_at_order);
        $stmt->bindParam(":custom_description", $custom_description_val, $custom_description_param_type); // [MODIFIED] Bind کردن پارامتر

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("OrderItem creation error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    public function readByOrderId($order_id) {
        $query = "SELECT oi.product_id, oi.quantity, oi.price_at_order, oi.custom_description, p.name as product_name, p.sku as product_sku 
                  FROM " . $this->table_name . " oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }
    
    public function deleteByOrderId($order_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE order_id = :order_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("OrderItem deletion by order ID error: " . $e->getMessage());
            return false;
        }
    }
}
?>
