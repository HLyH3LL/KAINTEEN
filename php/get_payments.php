<?php
include 'db.php';

$sort = $_GET['sort'] ?? 'date';   // default sort by date
$filter = $_GET['status'] ?? '';   // optional status filter

// Base query
$query = "SELECT p.id, o.order_code, p.student_no, p.status, 
                 DATE_FORMAT(p.payment_date, '%m/%d/%Y') as payment_date, p.mop
          FROM payments p
          JOIN orders o ON p.order_id = o.id";

// Add filter if selected
if ($filter) {
    $query .= " WHERE p.status = ?";
}

// Sorting
if ($sort === 'status') {
    $query .= " ORDER BY p.status ASC";
} else {
    $query .= " ORDER BY p.payment_date DESC";
}

$stmt = $conn->prepare($query);

if ($filter) {
    $stmt->bind_param("s", $filter);
}

$stmt->execute();
$result = $stmt->get_result();

$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

echo json_encode($payments);
?>