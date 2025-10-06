<?php
session_start();
include 'db.php';

// Ensure user logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../auth/signInStudent.html');
    exit;
}

// Handle adding items to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity < 1) $quantity = 1;

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add or update quantity in cart
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    header('Location: studentDashboard.php'); // Back to dashboard after adding
    exit;
}

// Handle "Proceed to Checkout" button
if (isset($_GET['action']) && $_GET['action'] === 'checkout') {
    if (empty($_SESSION['cart'])) {
        echo "Your cart is empty.";
        exit;
    } else {
        header('Location: payment.php');
        exit;
    }
}
