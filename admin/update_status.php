<?php

require_once "../backend/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$id = $_POST["id"] ?? "";
$status = $_POST["status"] ?? "";

$allowed_statuses = [
    "New",
    "Contacted",
    "Visited",
    "Closed"
];

if (empty($id) || !in_array($status, $allowed_statuses)) {
    die("Invalid data");
}

$sql = "UPDATE visits SET status = ? WHERE id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
} else {
    echo "Error updating status: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>