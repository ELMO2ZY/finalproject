-- Create ticket_history table if it doesn't exist
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
);

-- Add columns to tickets table if they don't exist
ALTER TABLE tickets 
ADD COLUMN status ENUM('open', 'pending', 'resolved') DEFAULT 'open' NOT NULL;

ALTER TABLE tickets 
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE tickets 
ADD COLUMN subject VARCHAR(255);

ALTER TABLE tickets 
ADD COLUMN description TEXT; 