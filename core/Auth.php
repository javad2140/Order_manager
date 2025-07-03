<?php

class Auth {
    private $conn; // Database connection

    public function __construct($db) {
        // Ensure $db is a valid PDO object
        if (!($db instanceof PDO)) {
            error_log("Auth::__construct received an invalid database connection. Type: " . gettype($db));
            // If it's not a PDO object, let's explicitly set conn to null to avoid further errors
            $this->conn = null;
            // Optionally, throw an exception to halt execution if this is a critical error
            // throw new InvalidArgumentException("Invalid database connection provided to Auth class.");
        } else {
            $this->conn = $db;
        }
    }

    public function register($username, $password, $email, $role) { // Added $email and $role
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if username already exists
        $checkQuery = "SELECT id FROM users WHERE username = :username LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        if ($checkStmt->rowCount() > 0) {
            return false; // Username already exists
        }

        $query = "INSERT INTO users (username, password, email, role) VALUES (:username, :password, :email, :role)";
        
        if (!$this->conn) {
            error_log("Database connection is not available in Auth::register.");
            return false;
        }

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind values
        $username = htmlspecialchars(strip_tags($username));
        $email = htmlspecialchars(strip_tags($email)); // Sanitize email

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email); // Bind email
        $stmt->bindParam(':role', $role);   // Bind role

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password FROM users WHERE username = :username LIMIT 0,1";
        
        // Ensure $this->conn is not null before calling prepare
        if (!$this->conn) {
            error_log("Database connection is not available in Auth::login.");
            return false;
        }

        $stmt = $this->conn->prepare($query);

        // Sanitize and bind value
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user was found and password verifies
        if ($user && is_array($user) && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        } else {
            // Log for debugging: if user not found or password mismatch
            if (!$user) {
                error_log("Login failed for username '{$username}': User not found.");
            } else {
                error_log("Login failed for username '{$username}': Password mismatch.");
            }
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    /**
     * Changes the password for a given user.
     *
     * @param int $userId The ID of the user whose password is to be changed.
     * @param string $currentPassword The user's current password.
     * @param string $newPassword The new password.
     * @return bool True on success, false on failure (e.g., current password mismatch, database error).
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        if (!$this->conn) {
            error_log("Database connection is not available in Auth::changePassword.");
            return false;
        }

        // Fetch current hashed password from database
        $query = "SELECT password FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user was found and current password is correct
        if (!$user || !is_array($user) || !password_verify($currentPassword, $user['password'])) {
            error_log("Password change failed for user ID {$userId}: Current password mismatch or user not found.");
            return false; // User not found or current password incorrect
        }

        // Hash the new password
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the password in the database
        $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
        $updateStmt = $this->conn->prepare($updateQuery);
        $updateStmt->bindParam(':password', $hashedNewPassword);
        $updateStmt->bindParam(':id', $userId, PDO::PARAM_INT);

        try {
            return $updateStmt->execute();
        } catch (PDOException $e) {
            error_log("Password change database error for user ID {$userId}: " . $e->getMessage());
            return false;
        }
    }
}
