<?php
require_once "controller/ProductController.php";
require_once "controller/CategoryController.php";
require_once "controller/UserController.php";
require_once "controller/CartController.php";
require_once "controller/ProductVariantController.php";
require_once "controller/ColorController.php";
require_once "controller/SizeController.php";
require_once "Controller/OrderController.php";
require_once "controller/HomeController.php";
require_once "controller/CouponController.php";

require_once "router/Router.php";

$router = new Router();
$productController = new ProductController();
$categoryController = new CategoryController();
$userController = new UserController();
$productVariantController = new ProductVariantController();
$colorController = new ColorController();
$sizeController = new SizeController();
$cartController = new CartController();
$orderController = new OrderController();
$homeController = new HomeController();
$couponController = new CouponController();

$router->addRoute("/", [$homeController, "index"]);

// Product routes
$router->addRoute("/products", [$productController, "index"]);
$router->addRoute("/products/create", [$productController, "create"]);
$router->addRoute("/products/{id}", [$productController, "show"]);
$router->addRoute("/products/edit/{id}", [$productController, "edit"]);
$router->addRoute("/products/delete/{id}", [$productController, "delete"]); 
//user
$router->addRoute("/product-list-user", [$productController, "userProductList"]); // Trang danh sách sản phẩm dành cho người dùng
// Category routes
$router->addRoute("/categories", [$categoryController, "index"]);
$router->addRoute("/categories/create", [$categoryController, "create"]);
$router->addRoute("/categories/{id}", [$categoryController, "show"]);
$router->addRoute("/categories/edit/{id}", [$categoryController, "edit"]);
$router->addRoute("/categories/delete/{id}", [$categoryController, "delete"]);

// User routes
$router->addRoute("/users", [$userController, "index"]);

// Đăng ký
$router->addRoute("/register", [$userController, "registerForm"]);
$router->addRoute("/register/submit", [$userController, "register"]);

// Đăng nhập
$router->addRoute("/login", [$userController, "loginForm"]);
$router->addRoute("/login/submit", [$userController, "login"]);

// Đăng xuất
$router->addRoute("/logout", [$userController, "logout"]);
//i4
$router->addRoute("/account", [$userController, "accountInfo"]);
$router->addRoute("/account/update", [$userController, "updateAccount"]);
$router->addRoute("/account/delete", [$userController, "deleteAccount"]);

//home
$router->addRoute("/", [$productController, "userProductList"]);




// Hiển thị ảnh phụ của sản phẩm
$router->addRoute("/products/{productId}/sub-images", [$productController, "showSubImages"]);
// Tạo ảnh phụ cho sản phẩm
$router->addRoute("/products/{productId}/sub-images/create", [$productController, "createSubImage"]);
$router->addRoute("/products/{productId}/sub-images/{subImageId}/edit", [$productController, "editSubImage"]);
$router->addRoute("/products/{productId}/sub-images/{subImageId}/delete", [$productController, "deleteSubImage"]);
//crart
// $router->addRoute("/cart/add", [$productController, "addToCart"]);
$router->addRoute("/cart/add", [$cartController, "addToCart"]); // Thêm sản phẩm vào giỏ hàng
$router->addRoute("/cart", [$cartController, "index"]);
$router->addRoute("/cart/update", [$cartController, "updateCart"]);
$router->addRoute("/cart/delete/{id}", [$cartController, "deleteCart"]);
$router->addRoute("/cart/delete-all", [$cartController, "deletecartAll"]);
$router->addRoute("/checkout", [$cartController, "checkout"]);

// router.php


// Dispatch all routes


// colors

$router->addRoute("/colors", [$colorController, "index"]); 
$router->addRoute("/colors/create", [$colorController, "create"]);
$router->addRoute("/colors/{id}", [$colorController, "show"]); 
$router->addRoute("/colors/edit/{id}", [$colorController, "edit"],); 
$router->addRoute("/colors/delete/{id}", [$colorController, "delete"], );

// sizes
$router->addRoute("/sizes", [$sizeController, "index"]);
$router->addRoute("/sizes/create", [$sizeController, "create"]);
$router->addRoute("/sizes/{id}", [$sizeController, "show"]);
$router->addRoute("/sizes/edit/{id}", [$sizeController, "edit"]);
$router->addRoute("/sizes/delete/{id}", [$sizeController, "delete"]);
# routers variant products
$router->addRoute("/orders", [$orderController, "index"]); // Danh sách đơn hàng
$router->addRoute("/orders/view/{id}", [$orderController, "show"]); // Xem chi tiết đơn hàng
$router->addRoute("/orders/create", [$orderController, "create"]); // Tạo đơn hàng mới
$router->addRoute("/orders/edit/{id}", [$orderController, "edit"]); // Sửa đơn hàng
$router->addRoute("/orders/delete/{id}", [$orderController, "delete"]); // Xóa đơn hàng
$router->addRoute("/orders/user/{userId}", [$orderController, "userOrders"]); // Đơn hàng của người dùng
$router->addRoute("/orders/dashboard", [$orderController, "dashboard"]); // Dashboard đơn hàng
$router->addRoute("/orders/success", [$orderController, "success"]); // Trang xác nhận đơn hàng
$router->addRoute("/orders/cancel-order-user/{id}", [$orderController, "cancelOrderUser"]);

// Định nghĩa routes cho orders

$router->addRoute("/orders/vnpay_return", [$orderController, "vnpayReturn"]); // Callback từ VNPAY
$router->addRoute("/orders/failed", [$orderController, "failed"]); // Xử lý thanh toán thất bại


$router->addRoute("/product-variants/create/{id}", [$productVariantController, "create"]);
$router->addRoute("/product-variants/list/{id}", [$productVariantController, "listVariants"]);
$router->addRoute("/product-variants/edit/{id}", [$productVariantController, "edit"]);
$router->addRoute("/product-variants/delete/{id}", [$productVariantController, "delete"]);

// Order Reviews

$router->addRoute('/orders/review', [$orderController, 'addReview'], 'POST');

//coupon
$router->addRoute("/coupons", [$couponController, "index"]);
$router->addRoute("/coupons/create", [$couponController, "create"]);
$router->addRoute("/coupons/{id}", [$couponController, "show"]);
$router->addRoute("/coupons/edit/{id}", [$couponController, "edit"]);
$router->addRoute("/coupons/delete/{id}", [$couponController, "delete"]);
$router->addRoute("/cart/apply-coupon", [$couponController, "applyCoupon"]);

$router->dispatch();

?>
