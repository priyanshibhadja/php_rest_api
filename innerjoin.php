<?php
header('Content-Type: application/json');
header('Acess-Control-Allow-Origin:*');

require_once "connection.php";

$request_method= $_SERVER["REQUEST_METHOD"];
/* This is for displaying all the data */
if($request_method == "GET"){

          $sql ="SELECT * FROM customer As c inner join payment As p on c.customer_id =p.customer_id";
          $result = mysqli_query($conn,$sql) or die("Sql query failed.");

          if(mysqli_num_rows($result)>0){
              $output = mysqli_fetch_all($result,MYSQLI_ASSOC);
              echo json_encode(array('message' => 'Records found.', 'status' => true, 'data' => $output));
          }
          else{
              echo json_encode(array('message' => 'No record found.','status' => false));

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