USE SkinBaazar; 


CREATE TABLE game_account_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES game_accounts(id) ON DELETE CASCADE
);