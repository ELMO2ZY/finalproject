<?php
require_once 'db_connect.php';

try {
    // Create ticket_history table
    $create_history_table = "
    CREATE TABLE IF NOT EXISTS ticket_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ticket_id INT NOT NULL,
        user_id INT NOT NULL,
        action VARCHAR(50) NOT NULL,
        new_value TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($create_history_table);
    echo "Ticket history table created successfully!\n";

    // Add columns to tickets table
    $columns = [
        "status" => "ALTER TABLE tickets ADD COLUMN status ENUM('open', 'pending', 'resolved') DEFAULT 'open' NOT NULL",
        "created_at" => "ALTER TABLE tickets ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "subject" => "ALTER TABLE tickets ADD COLUMN subject VARCHAR(255)",
        "description" => "ALTER TABLE tickets ADD COLUMN description TEXT"
    ];

    foreach ($columns as $column => $sql) {
        try {
            $pdo->exec($sql);
            echo "Added {$column} column to tickets table.\n";
        } catch (PDOException $e) {
            // Ignore error if column already exists
            if (strpos($e->getMessage(), "Duplicate column name") !== false) {
                echo "Column {$column} already exists.\n";
            } else {
                echo "Error adding {$column} column: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\nDatabase setup completed!";
    
} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
}
?> 