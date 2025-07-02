-- Tạo schema (cơ sở dữ liệu)
CREATE DATABASE IF NOT EXISTS VNSPORTS;

-- Sử dụng schema vừa tạo
USE VNSPORTS;

-- Tạo bảng `users`
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,       -- Auto-increment ID
    name VARCHAR(255) NOT NULL,              -- User's name
    email VARCHAR(255) NOT NULL UNIQUE,      -- Email (unique)
    phone VARCHAR(15) NOT NULL,              -- Phone number
    password VARCHAR(255) NOT NULL,          -- Hashed password
    address VARCHAR(255) DEFAULT NULL,       -- User's street address
    city VARCHAR(100) DEFAULT NULL,          -- User's city/province
    district VARCHAR(100) DEFAULT NULL,      -- User's district
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Creation timestamp
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY, -- ID sản phẩm (khóa chính)
    name VARCHAR(255) NOT NULL,        -- Tên sản phẩm
    image VARCHAR(255) NOT NULL,       -- Đường dẫn hình ảnh sản phẩm
    price DECIMAL(10, 2) NOT NULL,     -- Giá sản phẩm
    old_price DECIMAL(10, 2),          -- Giá gốc (nếu có)
    sku VARCHAR(50) NOT NULL,          -- Mã sản phẩm
    STTbrand int,
    brand VARCHAR(100),                -- Thương hiệu sản phẩm
    category VARCHAR(100) NOT NULL,    -- Danh mục sản phẩm
    warranty VARCHAR(50),              -- Thời gian bảo hành
    stock INT NOT NULL DEFAULT 0,      -- Số lượng tồn kho
    description TEXT,                  -- Mô tả sản phẩm
    specs TEXT,                        -- Thông số kỹ thuật
    promotion VARCHAR(255) DEFAULT NULL -- Ưu đãi sản phẩm (có thể để trống)
);
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,        -- ID đánh giá (khóa chính)
    product_id INT NOT NULL,                  -- ID sản phẩm (khóa ngoại)
    user_name VARCHAR(255) NOT NULL,          -- Tên người dùng
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5), -- Số sao (1-5)
    content TEXT NOT NULL,                    -- Nội dung đánh giá
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Thời gian tạo đánh giá
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE -- Liên kết với bảng products
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  full_name VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  email VARCHAR(255),
  address TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  district VARCHAR(100) NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  total_price DECIMAL(15,2) NOT NULL,
  shipping_fee DECIMAL(15,2) NOT NULL,
  final_total DECIMAL(15,2) NOT NULL,
  order_date DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  serial_number VARCHAR(100),        -- Mã số seri để kiểm tra bảo hành
  price DECIMAL(15,2) NOT NULL,
  quantity INT NOT NULL,
  warranty_expire_date DATE,                 -- Ngày hết hạn bảo hành
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
DELIMITER //

CREATE TRIGGER trg_decrease_stock_after_order
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
  UPDATE products
  SET stock = stock - NEW.quantity
  WHERE id = NEW.product_id;
END;
//
DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_increase_stock_after_delete
AFTER DELETE ON order_items
FOR EACH ROW
BEGIN
  UPDATE products
  SET stock = stock + OLD.quantity
  WHERE id = OLD.product_id;
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_check_stock_before_insert
BEFORE INSERT ON order_items
FOR EACH ROW
BEGIN
  DECLARE current_stock INT;

  SELECT stock INTO current_stock
  FROM products
  WHERE id = NEW.product_id;

  IF NEW.quantity > current_stock THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'Không đủ hàng trong kho để thực hiện đơn hàng.';
  END IF;
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_set_order_date_before_insert
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
  IF NEW.order_date IS NULL THEN
    SET NEW.order_date = NOW();
  END IF;
END;
//

DELIMITER ;

DELIMITER //

CREATE TRIGGER trg_set_final_total_before_insert
BEFORE INSERT ON orders
FOR EACH ROW
BEGIN
  IF NEW.final_total IS NULL THEN
    SET NEW.final_total = NEW.total_price + NEW.shipping_fee;
  END IF;
END;
//

DELIMITER ;
