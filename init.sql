DROP TABLE IF EXISTS customers, products, orders, order_items, inventory, order_logs;

CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL
);

CREATE TABLE inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT,
    quantity INT,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    created_at DATETIME,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE order_items (
     id INT PRIMARY KEY AUTO_INCREMENT,
     order_id INT,
     product_id INT,
     quantity INT,
     FOREIGN KEY (order_id) REFERENCES orders(id),
     FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE order_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    message TEXT,
    created_at DATETIME,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

INSERT INTO customers (name) VALUES ("John Doe"), ("Jane Smith");
INSERT INTO products (name, price) VALUES ("T-Shirt", 20.00), ("Jeans", 50.00);
INSERT INTO inventory (product_id, quantity) VALUES (1, 100), (2, 50);
