<?php

require_once "../backend/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$id = $_POST["id"] ?? "";

if (empty($id)) {
    die("Invalid lead ID");
}

$sql = "DELETE FROM visits WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: dashboard.php");
    exit;
} else {
    echo "Error deleting lead: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>