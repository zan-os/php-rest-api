<?php

// Require the necessary libraries
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;

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

// Check for a valid Content-Type header
if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    // Return error message
    http_response_code(400);
    echo json_encode(array(
    'message' => 'Content-Type must be application/json'
    ));
  exit;
}

// Read the request body
$body = file_get_contents('php://input');

// Decode the request body as JSON
$data = json_decode($body, true);

try {
    // Check the email and password against the database
    $query = $db->prepare('SELECT id,password FROM users WHERE email = :email OR username = :username');
    //binding parameters with json body
    $query->bindParam(':email', $data['email']);
    $query->bindParam(':username', $data['username']);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Return error message
    echo json_encode(['message' => 'Login failed because : ' . $e->getMessage()]);
    exit;
}

if (!$user || $data['password'] !=$user['password']) {
    // Return error message
    http_response_code(401);
    echo json_encode(array(
    'message' => 'Email or password is incorrect'
    ));
  exit;
}

// Create a JWT (JSON Web Token)
$secret = 'your_secret_key';
$issuedAt = time();
$expirationTime = $issuedAt + 60 * 60; // token is valid for 1 hour
$payload = array(
  'iat' => $issuedAt,
  'exp' => $expirationTime,
  'user_id' => $user['id']
);
$jwt = JWT::encode($payload, $secret, 'HS256');

// Return the JWT to the client
http_response_code(201);
echo json_encode(array(
    'access_token' => $jwt
));