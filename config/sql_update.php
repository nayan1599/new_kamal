ALTER TABLE `transactions` ADD `type` SET('in','out') NOT NULL AFTER `status`;
ALTER TABLE `rents` ADD `payment_status` VARCHAR(150) NOT NULL AFTER `payment_method`;
<!-- new table  -->
CREATE TABLE IF NOT EXISTS call_stories (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    date                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name                VARCHAR(150) NOT NULL,
    phone               VARCHAR(20) NOT NULL,
    car_number          VARCHAR(30),
    chassis_number      VARCHAR(50),
    total_kisti         INT,
    kisti_amount        DECIMAL(12,2),
    jabin_name          VARCHAR(150),
    jabin_phone         VARCHAR(20),
    call_status         VARCHAR(50),
    jabin_call_status   VARCHAR(50),
    note                TEXT,
    
    -- Added useful columns
    due_amount          DECIMAL(12,2),
    next_followup_date  DATE,
    promise_date        DATE,
    call_attempt        INT DEFAULT 1,
    call_category       VARCHAR(50),
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Optional: Add indexes for better performance
CREATE INDEX idx_phone ON call_stories(phone);
CREATE INDEX idx_car_number ON call_stories(car_number);
CREATE INDEX idx_date ON call_stories(date);
CREATE INDEX idx_next_followup ON call_stories(next_followup_date);

