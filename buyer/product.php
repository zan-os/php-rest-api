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

// Allow GET method
header("Access-Control-Allow-Methods: GET");

// Allow Content-Type and Authorization headers
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow credentials
header("Access-Control-Allow-Credentials: true");

// Return 200 OK if preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Return response messgae
    http_response_code(200);
    echo json_encode(['message' => 'OK']);
    exit;
}

// Check the request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    // Return error message
    http_response_code(405);
    echo json_encode(array(
    'message' => 'Method not allowed'));
    exit;
}

// Check if params id empty
if (!isset($_GET['id'])) {
    // Check if search product is success
    try {
        // Search all product from database
        $productQuery = $db->prepare('SELECT * FROM products');
        $productQuery->execute();
        $product = $productQuery->fetchAll(PDO::FETCH_ASSOC);
        // Return success message
        http_response_code(200);
        echo json_encode($product);
        exit;
    } catch (PDOException $e) {
        // Return error message
        echo json_encode(['message' => 'Get product failed because : ' . $e->getMessage()]);
        exit;
    }
    
}

// Check if id is numeric
if (!is_numeric($_GET['id'])) {
    // Return error message
    http_response_code(400);
    echo json_encode(['message' => 'Invalid product id']);
    exit;
}

// Check if query is success
try {
    // Search product by product id
    $productQuery = $db->prepare('SELECT * FROM products p INNER JOIN users u ON p.user_id = u.id WHERE p.id = :id');
    $productQuery->bindParam(':id', $_GET['id']);
    $productQuery->execute();
    $product = $productQuery->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Return error message
    echo json_encode(['message' => 'Get product detail failed because : ' . $e->getMessage()]);
    exit;
}

// Check if product exist
if(!$product) {
    //show error message
    http_response_code(400);
    echo json_encode(['message' => 'No product found']);
    exit;
}

// Return the success message
http_response_code(200);
echo json_encode([
    'id' => $product['id'],
    'name' => $product['name'],
    'description' => $product['description'],
    'price' => $product['price'],
    'created_at' => $product['created_at'],
    'user' => [
        'id' => $product['user_id'],
        'username' => $product['username'],
        'full_name' => $product['full_name'],
        'full_address' => $product['full_address'],
        'phone_number' => $product['phone_number']
    ]
]);