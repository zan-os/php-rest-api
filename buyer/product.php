<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
// Connect to the MySQL database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "my_api";

$conn = mysqli_connect($host, $user, $password, $dbname);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Select all products and associated user data
    $query = "SELECT * FROM products";
    $result = mysqli_query($conn, $query);
    
    // Fetch the data into an array
    $products = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
        $user_id = $row['user_id'];
        // $userQuery = "SELECT * FROM users WHERE id = $user_id";
        $useresult = mysqli_query($conn, $query);
    }
    
    $response = $products;
} else {
    // Return an error if the request method is not GET
    $response = array(
        "status" => "error",
        "message" => "Invalid request method"
    );
}

// Return the response in JSON format
header("Content-Type: application/json");
echo json_encode($response);

mysqli_close($conn);