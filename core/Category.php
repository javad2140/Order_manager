<?php

class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $description; // Added description property based on controller usage
    public $parent_id;   // Added parent_id property based on controller usage
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllCategories() {
        $query = "SELECT id, name, description, parent_id, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // New method to fetch categories in a hierarchical structure
    public function getAllCategoriesHierarchical() {
        // Fetch all categories
        $query = "SELECT id, name, description, parent_id FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Build the hierarchical tree
        $indexedCategories = [];
        foreach ($categories as $category) {
            $indexedCategories[$category['id']] = $category;
            $indexedCategories[$category['id']]['children'] = [];
        }

        $hierarchicalCategories = [];
        foreach ($indexedCategories as $id => $category) {
            if ($category['parent_id'] === null || !isset($indexedCategories[$category['parent_id']])) {
                // This is a top-level category
                $hierarchicalCategories[] = &$indexedCategories[$id];
            } else {
                // This is a child category
                $indexedCategories[$category['parent_id']]['children'][] = &$indexedCategories[$id];
            }
        }
        return $hierarchicalCategories;
    }

    public function getAllCategoriesFlat() {
        // This method can remain if needed for other parts of the application
        // For now, it just calls getAllCategories for a flat list
        return $this->getAllCategories();
    }


    public function getCategoryById($id) {
        $query = "SELECT id, name, description, parent_id, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, description, parent_id) VALUES (:name, :description, :parent_id)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        // Convert empty string to NULL for parent_id if it's empty
        $this->parent_id = empty($this->parent_id) ? null : htmlspecialchars(strip_tags($this->parent_id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':parent_id', $this->parent_id, PDO::PARAM_INT); // Bind as INT, or PDO::PARAM_NULL if null

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name, description = :description, parent_id = :parent_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        // Convert empty string to NULL for parent_id if it's empty
        $this->parent_id = empty($this->parent_id) ? null : htmlspecialchars(strip_tags($this->parent_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':parent_id', $this->parent_id, PDO::PARAM_INT); // Bind as INT, or PDO::PARAM_NULL if null
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // New method to check for subcategories
    public function hasSubcategories($category_id) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE parent_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    /**
     * [جدید] متد برای بررسی اینکه آیا محصولی از این دسته‌بندی استفاده می‌کند یا خیر
     * @param int $category_id شناسه دسته‌بندی
     * @return bool
     */
    public function hasProducts($category_id) {
        // نام جدول 'products' باید با نام جدول محصولات شما مطابقت داشته باشد
        $query = "SELECT COUNT(*) FROM products WHERE category_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
