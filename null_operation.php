<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once "connection.php";

$request_method = $_SERVER["REQUEST_METHOD"];

if ($request_method == "GET") 
{
    $id = $_GET['sid'];

    // Query to get column names
    $sql_columns = "SHOW COLUMNS FROM student";
    $result_columns = $conn->query($sql_columns);

    $response = array();
    $missing_columns = array(); // Store missing columns here

    // Check if the query executed successfully
    if ($result_columns) 
    {
        while ($row = $result_columns->fetch_assoc()) 
        {
            $column_name = $row['Field'];
            // Query to check if the specified row has NULL value in this column
            $check_null_sql = "SELECT COUNT(*) as count_null FROM student WHERE id = ? AND `$column_name` IS NULL"; // Enclose column name in backticks
            // Prepare the statement
            $stmt = $conn->prepare($check_null_sql);
            if ($stmt) 
            {
                // Bind the parameters
                $stmt->bind_param("i", $id); // Assuming id is an integer, if it's not, change "i" to "s" for string
                // Execute the statement
                $stmt->execute();
                // Get the result
                $null_result = $stmt->get_result();
                if ($null_result) 
                {
                    $null_row = $null_result->fetch_assoc();
                    $null_count = $null_row['count_null'];
                    if ($null_count > 0) 
                    {
                        $missing_columns[] = $column_name; // Add missing column to array
                    }
                } 
                else 
                {
                    // If the query fails, handle the error
                    $response[] = "Error executing query for column: " . $column_name;
                }
                // Close the statement
                $stmt->close();
            } 
            else 
            {
                // If preparing the statement fails, handle the error
                $response[] = "Error preparing statement for column: " . $column_name;
            }
        }
    } 
    else 
    {
        // If the query fails, handle the error
        $response[] = "Error executing query to fetch columns: " . $conn->error;
    }

    // Close connection
    $conn->close();

    // Check if there are missing columns and then echo the JSON response
    if (!empty($missing_columns)) 
    {
        echo json_encode(array('message' => 'Missing Information on specific rows', 'status' => true, 'data' => $missing_columns));
    } 
    else 
    {
        echo json_encode(array('message' => 'No missing information found for this row', 'status' => false));
    }
}
elseif ($request_method == "PUT") 
{
    // Get JSON input data
    $put_data = json_decode(file_get_contents("php://input"), true);

    // Extract parameters from JSON data
    $id = $put_data['sid'];
    $column_names = $put_data['column_names'];
    $new_values = $put_data['new_values'];

    // Check if the necessary parameters are provided
    if (empty($id) || empty($column_names) || empty($new_values)) 
    {
        echo json_encode(array('message' => 'Row ID, column names, and new values must be provided', 'status' => false));
        exit();
    }

    // Split column names and new values into arrays
    $column_names_array = explode(",", $column_names);
    $new_values_array = explode(",", $new_values);

    // Validate if the number of column names matches the number of new values
    if (count($column_names_array) !== count($new_values_array)) 
    {
        echo json_encode(array('message' => 'Number of column names does not match the number of new values', 'status' => false));
        exit();
    }

    // Construct and execute update queries for each column
    foreach ($column_names_array as $index => $column_name) 
    {
        $new_value = $new_values_array[$index];

        // Check if the column name and new value are provided
        if (empty($column_name) || empty($new_value)) 
        {
            echo json_encode(array('message' => 'Column name and new value must be provided for each column', 'status' => false));
            exit();
        }

        // Prepare the query to check if the specified row has NULL value in the specified column
        $check_null_sql = "SELECT $column_name FROM student WHERE id = ?";
        $stmt = $conn->prepare($check_null_sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $null_result = $stmt->get_result();

        if ($null_result) {
            $null_row = $null_result->fetch_assoc();
            $value = $null_row[$column_name];
            if (is_null($value)) 
            {
                // Prepare the update query
                $update_sql = "UPDATE student SET $column_name = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $new_value, $id);
                if ($stmt->execute()) 
                {
                    echo json_encode(array('message' => 'Record updated successfully for column: ' . $column_name, 'status' => true));
                } 
                else 
                {
                    // If the update query fails, handle the error
                    echo json_encode(array('message' => 'Error updating record for column: ' . $column_name . ', Error: ' . $conn->error, 'status' => false));
                }
            } 
            else 
            {
                // If the column is not NULL, inform the user
                echo json_encode(array('message' => 'Column already has a value, no update performed for column: ' . $column_name, 'status' => false));
            }
        } 
        else 
        {
            // If the query fails, handle the error
            echo json_encode(array('message' => 'Error executing query for column: ' . $column_name, 'status' => false));
        }
    }
    // Close connection
    $conn->close();
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
