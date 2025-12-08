-- dg599 12/6
CREATE TABLE IF NOT EXISTS UserCars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES Cars(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_car (user_id, car_id),
    INDEX idx_user_id (user_id),
    INDEX idx_car_id (car_id)
);