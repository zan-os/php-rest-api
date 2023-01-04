<?php
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
    // Return response code
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
if (empty($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] !== 'application/json') {
    // Return error message
    http_response_code(400);
    echo json_encode(array(
    'message' => 'Content-Type must be application/json'));
    exit;
}

// Read the request body
$body = file_get_contents('php://input');

// Decode the request body as JSON
$data = json_decode($body, true);

// Validate the request data
if (empty($data['email']) || empty($data['username']) || empty($data['password']) || empty($data['full_name']) || empty($data['phone_number']) || empty($data['full_address'])) {
    // Return error message
    http_response_code(400);
    echo json_encode(array(
    'message' => 'Fill all form'));
    exit;
}

// Validate email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    // Return error message
    http_response_code(400);
    echo json_encode(['message' => 'Invalid email']);
    exit;
}

// Validate password
if (strlen($data['password']) < 6) {
    // Return error message
    http_response_code(400);
    echo json_encode(['message' => 'Password must be at least 6 characters']);
    exit;
}

// Check if query is success
try {
   //search existed email or username
    $userQuery = $db->prepare('SELECT COUNT(*) as count FROM users WHERE email = :email OR username = :username');
    $userQuery->bindParam(':email', $data['email']);
    $userQuery->bindParam(':username', $data['username']);
    $userQuery->execute();
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Return error message
    echo json_encode(['message' => 'Count user failed because : ' . $e->getMessage()]);
    exit;
}

//check if user is exist
if ($user['count'] > 0) {
    // Return error message
    http_response_code(400);
    echo json_encode(array(
    'message' => 'Username or email already exist'));
    exit;
}

// Check if query is success
try {
    //insert inputed user data to db
    $query = $db->prepare('INSERT INTO users (email, username, password, full_name, phone_number, full_address)  VALUES (:email, :username, :password, :full_name, :phone_number, :full_address)');
    //binding parameters with json body
    $query->bindParam(':email', $data['email']);
    $query->bindParam(':username', $data['username']);
    $query->bindParam(':password', $data['password']);
    $query->bindParam(':full_name', $data['full_name']);
    $query->bindParam(':phone_number', $data['phone_number']);
    $query->bindParam(':full_address', $data['full_address']);
    $query->execute();
} catch (PDOException $e) {
    // Return error message
    echo json_encode(['message' => 'Register failed because : ' . $e->getMessage()]);
    exit;
}

//return the success message
http_response_code(201);
echo json_encode(array(
    'message' => 'Successfully Register'
));