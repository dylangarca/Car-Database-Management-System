-- dg599 11/24
CREATE TABLE IF NOT EXISTS `Cars` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  -- API identifier 
  `api_id` VARCHAR(100) DEFAULT NULL COMMENT 'Unique ID from API, NULL for manual entries',
  `is_api` TINYINT(1) DEFAULT 0 COMMENT '1 = from API, 0 = manual entry',
  
  -- Core car data
  `make` VARCHAR(100) NOT NULL,
  `model` VARCHAR(100) NOT NULL,
  `year` INT NOT NULL,
  
  -- Additional car details
  `type` VARCHAR(50) DEFAULT NULL,
  `image_url` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  
  -- User tracking
  `user_id` INT NOT NULL,
  
  -- timestamp columns
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  -- Foreign key
  FOREIGN KEY (`user_id`) REFERENCES `Users`(`id`) ON DELETE CASCADE,
  
  -- Indexes
  INDEX `idx_make` (`make`),
  INDEX `idx_year` (`year`),
  INDEX `idx_api_id` (`api_id`)
);