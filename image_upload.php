<?php

require_once "connection.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if file is uploaded successfully
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        // Get the uploaded file name
        $imageName = $_FILES["file"]["name"];

        // Check if the image name already exists in the database
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $checkStmt->bind_param("s", $imageName);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            // Image name already exists in the database
            echo json_encode(["success" => false, "error" => "Image name already exists"]);
        } else {
            // Store the image name in the database
            $insertStmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
            $insertStmt->bind_param("s", $imageName);
            $insertStmt->execute();

            // Move uploaded file to local folder
            $uploadDir = "uploads/";
            $uploadPath = $uploadDir . basename($imageName);
            move_uploaded_file($_FILES["file"]["tmp_name"], $uploadPath);

            // Close statements
            $insertStmt->close();
            $checkStmt->close();
            $conn->close();

            echo json_encode(["success" => true, "message" => "Successfully uploaded"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to upload file"]);
    }
} 
else
{
    $data = [
        'status' => 405,
        'message' => $requestMethod. 'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}

?>
