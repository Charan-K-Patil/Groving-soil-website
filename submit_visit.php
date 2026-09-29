<?php

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

$name = $_POST["name"] ?? "";
$phone = $_POST["phone"] ?? "";
$email = $_POST["email"] ?? "";
$visit_date = $_POST["visit_date"] ?? "";
$message = $_POST["message"] ?? "";

if (empty($name) || empty($phone) || empty($email)) {
    die("Please fill in all required fields.");
}

$sql = "INSERT INTO visits (name, phone, email, visit_date, message, status)
        VALUES (?, ?, ?, ?, ?, 'New')";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "sssss",
    $name,
    $phone,
    $email,
    $visit_date,
    $message
);

if ($stmt->execute()) {
    header("Location: ../index.html?success=1#visit");
    exit;
} else {
    echo "Error submitting request: " . $stmt->error;
}

$stmt->close();
$conn->close();

?>