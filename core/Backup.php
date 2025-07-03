<?php
// core/Backup.php

class Backup {
    private $db;
    private $host;
    private $db_name;
    private $username;
    private $password;

    public function __construct(PDO $db, $host, $db_name, $username, $password) {
        $this->db = $db;
        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Creates a full SQL backup of the database and initiates a download.
     */
    public function createBackup() {
        try {
            $tables = [];
            $result = $this->db->query("SHOW TABLES");
            while ($row = $result->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sql_content = "-- SQL Backup\n-- Generation Time: " . date('Y-m-d H:i:s') . "\n\n";
            $sql_content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Add DROP TABLE statement
                $sql_content .= "DROP TABLE IF EXISTS `$table`;\n";

                // Add CREATE TABLE statement
                $create_table_result = $this->db->query("SHOW CREATE TABLE `$table`");
                $create_table_row = $create_table_result->fetch(PDO::FETCH_ASSOC);
                $sql_content .= $create_table_row['Create Table'] . ";\n\n";
                $create_table_result->closeCursor();

                // Add INSERT statements for data
                $data_result = $this->db->query("SELECT * FROM `$table`");
                while ($row = $data_result->fetch(PDO::FETCH_ASSOC)) {
                    $sql_content .= "INSERT INTO `$table` VALUES(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            // Escape special characters
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    $sql_content .= implode(', ', $values) . ");\n";
                }
                $sql_content .= "\n";
                $data_result->closeCursor();
            }

            $sql_content .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Set headers for file download
            $backup_filename = 'backup-' . $this->db_name . '-' . date('Y-m-d_H-i-s') . '.sql';
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $backup_filename . '"');
            header('Content-Length: ' . strlen($sql_content));
            
            echo $sql_content;
            exit();

        } catch (Exception $e) {
            die("Error creating backup: " . $e->getMessage());
        }
    }

    /**
     * Restores the database from an uploaded SQL file.
     * @param string $filePath The temporary path of the uploaded SQL file.
     * @return bool True on success, false on failure.
     */
    public function restoreBackup($filePath) {
        try {
            $sql_content = file_get_contents($filePath);
            if ($sql_content === false) {
                return false;
            }

            // Execute the multi-query
            $this->db->exec($sql_content);
            return true;

        } catch (Exception $e) {
            error_log("Restore Error: " . $e->getMessage());
            return false;
        }
    }
}
