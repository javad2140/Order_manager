<?php

class Product {
    private $conn;
    private $table_name = "products";

    // Object properties
    public $id;
    public $category_id;
    public $name;
    public $sku;
    public $price;
    public $description;
    public $image_url;
    public $stock;
    public $is_active;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all products with pagination and optional filters
    public function readPaging($from_record_num, $records_per_page, $search_term = '', $category_filter_id = null, $status_filter = null, $sort_by = 'created_at', $sort_order = 'DESC') {
        
        $query = "SELECT p.id, p.name, p.sku, p.price, p.description, p.image_url, p.stock, p.is_active, p.created_at, p.updated_at, c.name as category_name, p.category_id 
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id";
        
        $conditions = [];
        $params_for_execute = []; // This array will hold parameters in the exact order of '?' placeholders

        // Build conditions and params dynamically
        if (!empty($search_term)) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if ($category_filter_id !== null && $category_filter_id !== '') {
            $conditions[] = "p.category_id = ?";
            $params_for_execute[] = $category_filter_id;
        }

        if ($status_filter !== null && ($status_filter == 0 || $status_filter == 1)) {
            $conditions[] = "p.is_active = ?";
            $params_for_execute[] = $status_filter;
        }

        // Append WHERE clause if conditions exist
        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        // Add ORDER BY clause
        $valid_sort_columns = ['id', 'name', 'price', 'stock', 'created_at']; 
        $sort_by = in_array($sort_by, $valid_sort_columns) ? $sort_by : 'created_at';
        $sort_order = (strtoupper($sort_order) === 'ASC' || strtoupper($sort_order) === 'DESC') ? $sort_order : 'DESC';
        $query .= " ORDER BY p." . $sort_by . " " . $sort_order;

        // Add LIMIT for pagination (using positional '?' placeholders)
        $query .= " LIMIT ?, ?";
        $params_for_execute[] = $from_record_num;
        $params_for_execute[] = $records_per_page;

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute($params_for_execute); // Execute with the array of actual parameters
            return $stmt;
        } catch (PDOException $e) {
            error_log("Product readPaging error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return false; 
        }
    }

    // Used for paging products and counting with filters
    public function countAll($search_term = '', $category_filter_id = null, $status_filter = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " p ";
        
        $conditions = [];
        $params_for_execute = []; // This array will hold parameters in the order of '?' placeholders

        if (!empty($search_term)) {
            $conditions[] = "(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)";
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
            $params_for_execute[] = '%' . $search_term . '%';
        }

        if ($category_filter_id !== null && $category_filter_id !== '') {
            $conditions[] = "p.category_id = ?";
            $params_for_execute[] = $category_filter_id;
        }

        if ($status_filter !== null && ($status_filter == 0 || $status_filter == 1)) {
            $conditions[] = "p.is_active = ?";
            $params_for_execute[] = $status_filter;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $stmt = $this->conn->prepare($query);
        
        try {
            $stmt->execute($params_for_execute); // Execute with the array of actual parameters
            $row = $stmt->fetch(PDO::FETCH_NUM);
            return $row[0];
        } catch (PDOException $e) {
            error_log("Product countAll error: " . $e->getMessage() . " Query: " . $query . " Params: " . print_r($params_for_execute, true));
            return 0; 
        }
    }

    // Read a single product by ID
    public function readOne() {
        $query = "SELECT p.id, p.name, p.sku, p.price, p.description, p.image_url, p.stock, p.is_active, p.created_at, p.updated_at, c.name as category_name, p.category_id
                  FROM " . $this->table_name . " p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        
        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // [تغییر اصلی برای رفع باگ]: به جای پر کردن ویژگی‌های شیء و بازگرداندن true/false،
            // آرایه associative شامل اطلاعات محصول را برمی‌گردانیم.
            // کنترلر از این آرایه برای پر کردن فرم استفاده خواهد کرد.
            return $row; // اگر محصولی یافت نشود، $row === false خواهد بود.
        } catch (PDOException $e) {
            error_log("Product readOne error: " . $e->getMessage());
            return false;
        }
    }

    // Create a new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, sku=:sku, price=:price, 
                      description=:description, image_url=:image_url, stock=:stock, is_active=:is_active";

        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        
        // Generate a unique SKU if not provided or empty
        if (empty($this->sku)) {
            $this->sku = 'SKU_' . uniqid() . '_' . time(); 
        }
        $this->sku = htmlspecialchars(strip_tags($this->sku)); 
        
        // Convert to float/int
        $this->price = filter_var($this->price, FILTER_VALIDATE_FLOAT); 
        $this->stock = filter_var($this->stock, FILTER_VALIDATE_INT); 

        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        
        // Handle category_id (can be NULL)
        $category_id_val = empty($this->category_id) ? null : (int)$this->category_id;
        $category_id_param_type = ($category_id_val === null) ? PDO::PARAM_NULL : PDO::PARAM_INT;

        // Handle is_active (boolean)
        $is_active_val = (int)$this->is_active; // Ensure it's 0 or 1 integer
        
        // Bind values
        $stmt->bindParam(":category_id", $category_id_val, $category_id_param_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT); 
        $stmt->bindParam(":is_active", $is_active_val, PDO::PARAM_INT); 

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Product creation error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Update an existing product
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET category_id=:category_id, name=:name, sku=:sku, price=:price, 
                      description=:description, image_url=:image_url, stock=:stock, is_active=:is_active, 
                      updated_at=CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize and prepare inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->sku = htmlspecialchars(strip_tags($this->sku)); 

        // Convert to float/int
        $this->price = filter_var($this->price, FILTER_VALIDATE_FLOAT); 
        $this->stock = filter_var($this->stock, FILTER_VALIDATE_INT); 

        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->id = htmlspecialchars(strip_tags($this->id)); // ID هم باید Sanitize شود
        
        // Handle category_id (can be NULL)
        $category_id_val = empty($this->category_id) ? null : (int)$this->category_id;
        $category_id_param_type = ($category_id_val === null) ? PDO::PARAM_NULL : PDO::PARAM_INT;

        // Handle is_active (boolean)
        $is_active_val = (int)$this->is_active; // Ensure it's 0 or 1 integer

        // Bind values
        $stmt->bindParam(":category_id", $category_id_val, $category_id_param_type);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":sku", $this->sku);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT); 
        $stmt->bindParam(":is_active", $is_active_val, PDO::PARAM_INT); 
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Product update error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Delete a product
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id)); // ID هم باید Sanitize شود
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT); 
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Product deletion error: " . $e->getMessage());
            return false;
        }
        return false;
    }
}
