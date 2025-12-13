-- Database setup cho CK_MNM


-- Bảng sản phẩm điện thoại
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    description VARCHAR(255),
    image_url VARCHAR(255),
    quantity_in_stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu
INSERT INTO products (name, price, description, quantity_in_stock) VALUES
('iPhone 15 Pro', 25000000, 'Điện thoại cao cấp Apple', 10),
('Samsung Galaxy S24', 22000000, 'Điện thoại flagship Samsung', 12),
('Google Pixel 8', 20000000, 'Điện thoại Google cao cấp', 8),
('Xiaomi 14 Ultra', 16000000, 'Điện thoại Xiaomi flagship', 15),
('OnePlus 12', 15000000, 'Điện thoại OnePlus mạnh mẽ', 10),
('Oppo Find X7', 18000000, 'Điện thoại Oppo cao cấp', 7);
