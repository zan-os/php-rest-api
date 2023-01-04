<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
// Connect to the MySQL database
$host = "localhost";
$user = "root";
$password = "";
$dbname = "my_api";

$conn = mysqli_connect($host, $user, $password, $dbname);

$conn = mysqli_connect($host, $user, $password, $dbname);

// Check the connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle the product upload process
    $auth_token = mysqli_real_escape_string($conn, $_POST["auth_token"]);
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $description = mysqli_real_escape_string($conn, $_POST["description"]);
    $price = mysqli_real_escape_string($conn, $_POST["price"]);
    
    // Check if the auth_token is valid
    $query = "SELECT * FROM users WHERE auth_token='$auth_token'";
    $result = mysqli_query($conn, $query);
    $num_rows = mysqli_num_rows($result);
    
    if ($num_rows > 0) {
        // The auth_token is valid
        $row = mysqli_fetch_assoc($result);
        $user_id = $row["id"];
        $user_name = $row["username"];
        $user_city = $row["city"];
        $user_phone_number = $row["phone_number"];
        $user_address = $row["address"];
        
        // Insert the product and user data into the database
        $query = "INSERT INTO products (user_id, name, description, price, user_name, user_city, user_phone_number, user_address) VALUES ('$user_id', '$name', '$description', '$price', '$user_name', '$user_city', '$user_phone_number', '$user_address')";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            // Product added successfully
            http_response_code(201);
            $response = array(
                "status" => "success",
                "message" => "Product added successfully!"
            );
        } else {
            // Error adding product
            http_response_code(500);
            $response = array(
                "status" => "error",
                "message" => "Error adding product"
            );
        }
    } else {
        // The auth_token is invalid
        http_response_code(401);
        $response = array(
            "status" => "error",
            "message" => "Invalid auth_token"
        );
    }
} else {
    // Return an error if the request method is not POST
    http_response_code(405);
    $response = array(
        "status" => "error",
        "message" => "Invalid request method"
    );
}

// Return the response in JSON format
header("Content-Type: application/json");
echo json_encode($response);

mysqli_close($conn);