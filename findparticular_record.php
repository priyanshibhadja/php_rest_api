<?php
// Assuming your data source is a MySQL database
require_once "connection.php";
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method === 'GET') {
// Get the first letter from the input
$first_letter = isset($_GET['first_letter']) ? $_GET['first_letter'] : '';

// Prepare SQL statement to fetch records based on the first letter
$sql = "SELECT * FROM students WHERE LEFT(student_name, 1) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $first_letter);
$stmt->execute();
$result = $stmt->get_result();

$records = array();

// Check if there are any records
if ($result->num_rows > 0) {
    // Loop through the results and store them in the array
    while($row = $result->fetch_assoc()) {
        $records[] = array(
            "name" => $row["student_name"],
            "age" => $row["age"],
            "city" => $row["city"]
        );
    }
    echo json_encode(array('message' => 'Records found.', 'status' => true, 'data' => $records));
}
 else {
    echo json_encode(array('message' => 'No records found.', 'status' => false));
}

// Close connection
$conn->close();
}
else
{
    $data = [
        'status' => 405,
        'message' => $request_method. 'Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>
