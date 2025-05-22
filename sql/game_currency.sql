USE SkinBaazar;

CREATE TABLE game_currency (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_name VARCHAR(255) NOT NULL,
    currency_name VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255),
    added_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);