<?php

// Require the necessary libraries
require_once('../vendor/autoload.php');
use \Firebase\JWT\JWT;

// Set up the database connection
$db = new PDO('mysql:host=localhost;dbname=my_api', 'root', '');

// Set up CORS (Cross-Origin Resource Sharing) headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Check the request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(array('error' => 'Method not allowed'));
  exit;
}

// Check for a valid Content-Type header
if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
  http_response_code(400);
  echo json_encode(array('error' => 'Content-Type must be application/json'));
  exit;
}

// Read the request body
$body = file_get_contents('php://input');

// Decode the request body as JSON
$data = json_decode($body, true);

// Validate the request data
if (!isset($data['email']) || !isset($data['password'])) {
  http_response_code(400);
  echo json_encode(array('error' => 'Email and password are required'));
  exit;
}

// Check the email and password against the database
$query = $db->prepare('SELECT password FROM users WHERE email = :email OR username = :username');
$query->bindParam(':email', $data['email']);
$query->bindParam(':username', $data['username']);
$query->execute();
$user = $query->fetch();

if (!$user || $data['password']!=$user['password']) {
  http_response_code(401);
  echo json_encode(array('error' => 'Email or password is incorrect'));
  exit;
}

// Create a JWT (JSON Web Token)
$secret = 'your_secret_key';
$issuedAt = time();
$expirationTime = $issuedAt + 60 * 60; // token is valid for 1 hour
$payload = array(
  'iat' => $issuedAt,
  'exp' => $expirationTime,
  // 'user_id' => $user['id']
);
$jwt = JWT::encode($payload, $secret, 'HS256');

// Return the JWT to the client
echo json_encode(array('access_token' => $jwt));
