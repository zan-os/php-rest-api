<?php
// Require the necessary libraries
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Check if the connection was successful
try {
    $db = new PDO('mysql:host=localhost;dbname=my_api', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
    // Connection was successful
} catch (PDOException $e) {
    // Connection failed
    echo json_encode(['message' => 'Connection to the database failed because: ' . $e->getMessage()]);
    exit;
}

// Set up CORS (Cross-Origin Resource Sharing) headers
// Allow requests from any origin
header("Access-Control-Allow-Origin: *");

// Allow POST method
header("Access-Control-Allow-Methods: POST");

// Allow Content-Type and Authorization headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow credentials
header("Access-Control-Allow-Credentials: true");

// Return 200 OK if preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return response messgae
    http_response_code(200);
    exit;
}

// Check the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Return error message
    http_response_code(405);
    echo json_encode(array(
    'message' => 'Method not allowed'));
    exit;
}

// Read the request body
$body = file_get_contents('php://input');

// Decode the request body as JSON
$data = json_decode($body, true);

// Validate the request payload and return an error if the required fields are missing
if (empty($data['name']) || empty($data['description']) || empty($data['price']) || empty($data['quantity'])) {
  http_response_code(400);
  echo json_encode(['message' => 'Fill all form']);
  exit;
}

// Check if quantity value is numeric
if (!is_numeric($data['quantity'])) {
    // Return error message
    http_response_code(401);
    echo json_encode(array('message' => 'Invalid quantity value'));
    exit;
}

// Check if price value is numeric
if (!is_numeric($data['price'])) {
    // Return error message
    http_response_code(401);
    echo json_encode(array('message' => 'Invalid price value'));
    exit;
}

// Get all headers
$headers = getallheaders();
$jwt = $headers['Authorization'];

// If no JWT was provided, return an error
if (!$jwt) {
  http_response_code(401);
  echo json_encode(array('error' => 'No JWT provided'));
  exit;
}

// Decode the JWT and get the user ID
try {
    $secret = 'your_secret_key';
    $decoded = JWT::decode($jwt, new Key($secret, 'HS256'));
    $user_id = $decoded->user_id;
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(array('error' => 'Invalid JWT' . $e->getMessage()));
    exit;
}

// Check if query is success
try {
    // Insert product data into database
    $query = $db->prepare('INSERT INTO products (user_id, name, description, price, quantity, created_at) VALUES (:user_id, :name, :description, :price, :quantity, NOW())');
    //binding parameters with json body
    $query->bindParam(':user_id', $user_id);
    $query->bindParam(':name', $data['name']);
    $query->bindParam(':description', $data['description']);
    $query->bindParam(':price', $data['price']);
    $query->bindParam(':quantity', $data['quantity']);
    $query->execute();
} catch (PDOException $e) {
    // Return error message
    $errorInfo = $query->errorInfo();
    echo json_encode(['message' => 'Upload product failed because : ' . $e->getMessage()]);
    exit;
}

// Return success message
http_response_code(201);
echo json_encode(['message' => 'Product uploaded successfully']);