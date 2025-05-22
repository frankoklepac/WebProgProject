USE SkinBaazar;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,           
    account_id INT,                  
    currency_id INT,                 
    amount INT,                      
    price DECIMAL(10,2) NOT NULL,
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (account_id) REFERENCES game_accounts(id),
    FOREIGN KEY (currency_id) REFERENCES game_currency(id)
);