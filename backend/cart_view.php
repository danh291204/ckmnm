<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý xóa sản phẩm
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header('Location: cart_view.php');
    exit();
}

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    } elseif ($quantity <= 0) {
        unset($_SESSION['cart'][$product_id]);
    }
    header('Location: cart_view.php');
    exit();
}

// Xử lý xóa hết giỏ hàng
if (isset($_GET['clear_cart'])) {
    $_SESSION['cart'] = [];
    header('Location: cart_view.php');
    exit();
}

$cart_items = $_SESSION['cart'];
$grand_total = 0;

foreach ($cart_items as $item) {
    $grand_total += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng Của Bạn</title>
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
            max-width: 1000px;
            margin: 0 auto;
        }
        
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .back-link {
            color: #3498db;
            text-decoration: none;
            font-size: 16px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .cart-table th {
            background-color: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .product-name {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .price-cell {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            text-align: center;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-update {
            background-color: #3498db;
            color: white;
            margin-right: 5px;
        }
        
        .btn-update:hover {
            background-color: #2980b9;
        }
        
        .btn-remove {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-remove:hover {
            background-color: #c0392b;
        }
        
        .summary {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 16px;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-total {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
            margin-top: 15px;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background-color: #27ae60;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            flex: 1;
        }
        
        .btn-primary:hover {
            background-color: #229954;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        footer {
            text-align: center;
            color: #7f8c8d;
            padding: 20px;
            border-top: 1px solid #bdc3c7;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🛒 Giỏ Hàng Của Bạn</h1>
            <a href="products.php" class="back-link">⬅️ Tiếp tục mua sắm</a>
        </header>
        
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">
                📭 Giỏ hàng của bạn đang trống. <a href="products.php">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Tên Sản Phẩm</th>
                        <th style="text-align: right;">Giá</th>
                        <th style="text-align: center;">Số Lượng</th>
                        <th style="text-align: right;">Thành Tiền</th>
                        <th style="text-align: center;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $product_id => $item): 
                        $subtotal = $item['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td class="product-name"><?php echo htmlspecialchars($item['name']); ?></td>
                        <td style="text-align: right; color: #e74c3c; font-weight: bold;">
                            ₫ <?php echo number_format($item['price'], 0, ',', '.'); ?>
                        </td>
                        <td style="text-align: center;">
                            <form method="POST" style="display: flex; gap: 5px; justify-content: center;">
                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                <input type="hidden" name="update" value="1">
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" class="quantity-input">
                                <button type="submit" class="btn btn-update">Cập nhật</button>
                            </form>
                        </td>
                        <td style="text-align: right; color: #e74c3c; font-weight: bold;">
                            ₫ <?php echo number_format($subtotal, 0, ',', '.'); ?>
                        </td>
                        <td style="text-align: center;">
                            <a href="cart_view.php?remove=<?php echo $product_id; ?>" 
                               class="btn btn-remove"
                               onclick="return confirm('Xác nhận xóa sản phẩm này?')">🗑️ Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="summary">
                <div class="summary-row">
                    <span>Tổng số sản phẩm:</span>
                    <span><?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Tổng cộng:</span>
                    <span>₫ <?php echo number_format($grand_total, 0, ',', '.'); ?></span>
                </div>
            </div>
            
            <div class="actions">
                <a href="products.php" class="btn btn-primary">🛍️ Tiếp tục mua sắm</a>
                <a href="cart_view.php?clear_cart=1" class="btn btn-secondary" 
                   onclick="return confirm('Xóa hết tất cả sản phẩm?')">🔄 Xóa hết giỏ hàng</a>
            </div>
            
            <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 20px;">
                <p style="color: #856404; font-size: 16px;">
                    📞 Liên hệ với chúng tôi để hoàn tất đơn hàng | Hotline: 0123.456.789
                </p>
            </div>
        <?php endif; ?>
        
        <footer>
            <p>&copy; 2025 Cửa Hàng Điện Thoại. Tất cả quyền được bảo lưu.</p>
        </footer>
    </div>
</body>
</html>