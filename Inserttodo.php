<?php
$conn = new mysqli('localhost', 'root', '', 'logistics_db');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if (isset($_POST['task']) && isset($_POST['selectedDate'])) {
    $task = $_POST['task'];
    $selectedDate = $_POST['selectedDate'];

    $stmt = $conn->prepare("INSERT INTO tasks (task, selected_date) VALUES (?, ?)");
    $stmt->bind_param("ss", $task, $selectedDate);

    if ($stmt->execute()) {
        $last_id = $conn->insert_id; // Get inserted ID
        echo json_encode(["success" => true, "id" => $last_id]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
}
?>