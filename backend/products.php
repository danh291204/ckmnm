<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
session_start();
require 'condb.php';
//Test deploy
// Khởi tạo giỏ hàng
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    try {
        $stmt = $pdo->prepare('SELECT id, name, price FROM products WHERE id = ?');
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => 1
                ];
            }
            $_SESSION['add_success'] = true;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Lỗi khi thêm sản phẩm: ' . $e->getMessage();
    }
    
    header('Location: products.php');
    exit();
}

// Xử lý xóa sản phẩm khỏi giỏ hàng
if (isset($_GET['remove_product'])) {
    $product_id = (int)$_GET['remove_product'];
    unset($_SESSION['cart'][$product_id]);
    header('Location: products.php');
    exit();
}

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quantity'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    } elseif ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
    }
    
    header('Location: products.php');
    exit();
}

// Lấy danh sách sản phẩm
function get_all_products($pdo) {
    try {
        $stmt = $pdo->query('SELECT id, name, price, description FROM products');
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

$products = get_all_products($pdo);
$cart_total_items = array_sum(array_column($_SESSION['cart'], 'quantity'));
$cart_total_price = array_sum(array_map(function($item) {
    return $item['price'] * $item['quantity'];
}, $_SESSION['cart']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng Điện Thoại vui vui vui</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-size: 28px;
        }
        
        .cart-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .cart-link {
            background-color: #e74c3c;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .cart-link:hover {
            background-color: #c0392b;
        }
        
        .cart-count {
            background-color: #e74c3c;
            padding: 5px 10px;
            border-radius: 50%;
            font-weight: bold;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .product-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 16px rgba(0,0,0,0.2);
        }
        
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .product-description {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 15px;
            min-height: 40px;
        }
        
        .product-price {
            font-size: 24px;
            color: #e74c3c;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .product-price-unit {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        .btn-add-cart {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }
        
        .btn-add-cart:hover {
            background-color: #229954;
        }
        
        .btn-add-cart:active {
            transform: scale(0.98);
        }
        
        footer {
            text-align: center;
            color: #7f8c8d;
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div>
                <h1>🛍️ Cửa Hàng Điện Thoại</h1>
                <p>Bộ sưu tập điện thoại cao cấp</p>
            </div>
            <div class="cart-info">
                <div>
                    <p style="margin-bottom: 5px;">Giỏ hàng của bạn</p>
                    <p style="font-size: 14px; opacity: 0.9;">₫ <?php echo number_format($cart_total_price, 0, ',', '.'); ?></p>
                </div>
                <a href="cart_view.php" class="cart-link">
                    🛒 Giỏ hàng 
                    <?php if ($cart_total_items > 0): ?>
                        <span class="cart-count"><?php echo $cart_total_items; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </header>
        
        <?php if (isset($_SESSION['add_success'])): ?>
            <div class="alert alert-success">
                ✅ Sản phẩm đã được thêm vào giỏ hàng thành công!
            </div>
            <?php unset($_SESSION['add_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                ❌ <?php echo htmlspecialchars($_SESSION['error']); ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <h2 style="color: #2c3e50; margin-bottom: 20px;">Danh Sách Sản Phẩm</h2>
        
        <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-description">
                            <?php echo htmlspecialchars($product['description'] ?? 'Sản phẩm chất lượng cao'); ?>
                        </div>
                        <div class="product-price">
                            ₫ <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            <span class="product-price-unit">(VNĐ)</span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" class="btn-add-cart">➕ Thêm vào giỏ hàng</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-error">
                ❌ Không có sản phẩm nào. Vui lòng kiểm tra kết nối database.
            </div>
        <?php endif; ?>
        
        <footer>
            <p>&copy; 2025 Cửa Hàng Điện Thoại. Tất cả quyền được bảo lưu.</p>
        </footer>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh Sách Sản Phẩm (PHP View)</title>
</head>
<body>

    <h1>Danh Sách Sản Phẩm (Được tạo bởi PHP Backend)</h1>

    <p style="border: 1px solid #ccc; padding: 10px; background-color: #f9f9f9;">
        <a href="cart_view.php">🛒 Xem Giỏ Hàng</a> (Tổng: **<?php echo $cart_total_items; ?>** sản phẩm)
    </p>
    <p><a href="../frontend/index.html">⬅️ Quay lại Trang chủ HTML</a></p>

    <?php if (isset($_GET['add_success'])): ?>
        <p style="color: green;">Sản phẩm đã được thêm vào giỏ hàng thành công!</p>
    <?php endif; ?>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div style="border: 1px solid #000; padding: 15px; margin-bottom: 20px;">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p>Giá: **<?php echo number_format($product['price'], 0, ',', '.'); ?>** VNĐ</p>
                
                <form action="products.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit">Thêm vào Giỏ hàng</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>