<?php
header('Content-Type: application/json');
header('Acess-Control-Allow-Origin:*');
header('Acess-Control-Allow-Methods:POST');
header('Acess-Control-Allow-Methods:PUT');
header('Acess-Control-Allow-Methods:DELETE');
header('Acess-Control-Allow-Headers:Acess-Control-Allow-Headers,Content-Type,Acess-Control-Allow-Methods,Authorization, X-Requested-With');

require_once "connection.php";

$request_method = $_SERVER["REQUEST_METHOD"];

/* This is for displaying all the data */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['sid'])) {
        // If 'sid' parameter is provided, retrieve a specific record
        $id = $_GET['sid'];
        $sql = "SELECT * FROM students WHERE id = ?";
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bind_param("i", $id); // Assuming id is an integer
        
        // Execute the statement
        $stmt->execute();
        
        // Get the result set
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) 
        {
            // Fetch the data
            $output = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(array('message' => 'Record found.', 'status' => true, 'data' => $output));
        } 
        else 
        {
            echo json_encode(array('message' => 'No record found for the provided ID.', 'status' => false));
        }
    } 
    else 
    {
        // If 'sid' parameter is not provided, display all records
        $sql = "SELECT * FROM students";
        
        // Prepare the statement
        $stmt = $conn->prepare($sql);
        
        // Execute the statement
        $stmt->execute();
        
        // Get the result set
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Fetch all records
            $output = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(array('message' => 'All records displayed.', 'status' => true, 'data' => $output));
        } else {
            echo json_encode(array('message' => 'No records found.', 'status' => false));
        }
    }
}

 elseif ($request_method == "POST") {
     // Assuming form fields are sent as form-data
     $name = $_POST['sname'];
     $age = $_POST['sage'];
     $city = $_POST['scity'];

     if (!empty($name) && !empty($age) && !empty($city)) {
         $sql = "INSERT INTO students (student_name, age, city) VALUES (?, ?, ?)";
        
         // Prepare the statement
         $stmt = $conn->prepare($sql);
        
         // Bind parameters
         $stmt->bind_param("sis", $name, $age, $city);
        
         // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(array('message' => 'Student record inserted.', 'status' => true));
         } else {
             echo json_encode(array('message' => 'Failed to insert record.', 'status' => false));
         }
        
         // Close statement
         $stmt->close();
     } 
     else {
        echo json_encode(array('message' => 'Missing required fields.', 'status' => false));
    }
}

elseif ($request_method == "PUT") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['sid']) && isset($data['sname']) && isset($data['sage']) && isset($data['scity'])) {
        $student_id = $data['sid'];
        $name = $data['sname'];
        $age = $data['sage'];
        $city = $data['scity'];

        // Prepare the statement to check if the ID exists
        $check_query = "SELECT * FROM students WHERE id = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("i", $student_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Student ID exists, proceed with the update
            $sql = "UPDATE students SET student_name = ?, age = ?, city = ? WHERE id = ?";
            $stmt_update = $conn->prepare($sql);
            $stmt_update->bind_param("sisi", $name, $age, $city, $student_id);
            
            if ($stmt_update->execute()) {
                echo json_encode(array('message' => 'Student record updated.', 'status' => true));
            } else {
                echo json_encode(array('message' => 'Failed to update record.', 'status' => false));
            }

            // Close the update statement
            $stmt_update->close();
        } else {
            // Student ID does not exist
            echo json_encode(array('message' => 'Student ID does not exist.', 'status' => false));
        }

        // Close the check statement
        $stmt_check->close();
    } else {
        // Student ID or other fields are missing
        echo json_encode(array('message' => 'Missing required fields.', 'status' => false));
    }
}

//use prepared statment
elseif ($request_method == "DELETE") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['sid'])) {
        $student_id = $data['sid'];

        // Prepare the statement to check if the student ID exists
        $check_query = "SELECT * FROM students WHERE id = ?";
        $stmt_check = $conn->prepare($check_query);
        $stmt_check->bind_param("i", $student_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            // Student ID exists, proceed with the delete
            $sql = "DELETE FROM students WHERE id = ?";
            $stmt_delete = $conn->prepare($sql);
            $stmt_delete->bind_param("i", $student_id);

            if ($stmt_delete->execute()) {
                echo json_encode(array('message' => 'Student record deleted.', 'status' => true));
            } else {
                echo json_encode(array('message' => 'Failed to delete record.', 'status' => false));
            }

            // Close the delete statement
            $stmt_delete->close();
        } else {
            // Student ID does not exist
            echo json_encode(array('message' => 'Student ID does not exist.', 'status' => false));
        }

        // Close the check statement
        $stmt_check->close();
    } else {
        // Student ID is missing
        echo json_encode(array('message' => 'Missing student ID.', 'status' => false));
    }
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