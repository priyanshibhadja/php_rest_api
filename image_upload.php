<?php

require_once "connection.php";

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if file is uploaded successfully
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
        // Get the uploaded file name
        $image_name = $_FILES["file"]["name"];

        // Check if the image name already exists in the database
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $check_stmt->bind_param("s", $image_name);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Image name already exists in the database
            echo json_encode(["success" => false, "error" => "Image name already exists"]);
        } else {
            // Store the image name in the database
            $insert_stmt = $conn->prepare("INSERT INTO users (name) VALUES (?)");
            $insert_stmt->bind_param("s", $image_name);
            $insert_stmt->execute();

            // Move uploaded file to local folder
            $upload_dir = "uploads/";
            $upload_path = $upload_dir . basename($image_name);
            move_uploaded_file($_FILES["file"]["tmp_name"], $upload_path);

            // Close statements
            $insert_stmt->close();
            $check_stmt->close();
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
