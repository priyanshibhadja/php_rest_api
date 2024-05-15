<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once "connection.php";

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET") {
    $sql = "SELECT * FROM customer AS c LEFT JOIN payment AS p ON c.customer_id = p.customer_id
          UNION
          SELECT * FROM customer AS c RIGHT JOIN payment AS p ON c.customer_id = p.customer_id
          WHERE c.customer_id IS NULL OR p.customer_id IS NULL";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $output = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(array('message' => 'Records found.', 'status' => true, 'data' => $output));
    } else {
        echo json_encode(array('message' => 'No record found.', 'status' => false));
    }
} else {
    $data = [
        'status' => 405,
        'message' => $request_method . ' Method Not Allowed',
    ];
    header("HTTP/1.0 405 Method Not Allowed");
    echo json_encode($data);
}
?>
