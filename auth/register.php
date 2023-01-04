<?php
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
if (!isset($data['email']) || !isset($data['username']) || !isset($data['password']) || !isset($data['full_name']) || !isset($data['phone_number']) || !isset($data['full_address'])) {
  http_response_code(400);
  echo json_encode(array('error' => 'Fill all form'));
  exit;
}

//search existed email or username
$userQuery = $db->prepare('SELECT * FROM users WHERE email = :email OR username = :username');
$userQuery->bindParam(':email', $data['email']);
$userQuery->bindParam(':username', $data['username']);
$userQuery->execute();
$user = $userQuery->fetch();

if (!$user || $data['email'] != $user['email'] || $data['username'] != $user['username']) {
    //insert inputed user data to db
    $query = $db->prepare('INSERT INTO users (email, username, password, full_name, phone_number, full_address)  VALUES (:email, :username, :password, :full_name, :phone_number, :full_address)');
    $query->bindParam(':email', $data['email']);
    $query->bindParam(':username', $data['username']);
    $query->bindParam(':password', $data['password']);
    $query->bindParam(':full_name', $data['full_name']);
    $query->bindParam(':phone_number', $data['phone_number']);
    $query->bindParam(':full_address', $data['full_address']);
    $query->execute();

    echo json_encode(array(
        'status' => 'success',
        'message' => 'Successfully Register'
    ));
} else {
    // Check the email and password against the database
    http_response_code(401);
  echo json_encode($user);
  echo json_encode(array('error' => 'Username or email already exist'));
  exit;
}

// Return the JWT to the client
