<?php
/**
 * Duplicate Book Check
 * GET ?title=... or ?isbn=...
 * Returns JSON { duplicate: bool, book_id, book_title, quantity }
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config.php';
require_once 'auth.php';
requireLogin();

header('Content-Type: application/json');

$conn  = getDBConnection();
$title = trim($_GET['title'] ?? '');
$isbn  = trim($_GET['isbn'] ?? '');

if (empty($title) && empty($isbn)) {
    echo json_encode(['duplicate' => false]);
    closeDBConnection($conn);
    exit();
}

if ($isbn) {
    $safe = $conn->real_escape_string($isbn);
    $result = $conn->query("SELECT book_id, title, quantity FROM Books WHERE isbn = '$safe' LIMIT 1");
} else {
    $safe = $conn->real_escape_string($title);
    $result = $conn->query("SELECT book_id, title, quantity FROM Books WHERE LOWER(title) = LOWER('$safe') LIMIT 1");
}

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'duplicate'   => true,
        'book_id'     => $row['book_id'],
        'book_title'  => $row['title'],
        'quantity'    => $row['quantity'],
    ]);
} else {
    echo json_encode(['duplicate' => false]);
}

closeDBConnection($conn);
