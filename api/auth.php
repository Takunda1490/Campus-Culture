<?php
// Set JSON response header for all outputs
header('Content-Type: application/json');
// Load database connection (starts session inside)
require_once '../config/db.php';

// Determine HTTP method and optional action parameter
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            // Accept JSON body or form-data; merge action from body if present
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $action = $input['action'] ?? $action;

            // ---------- REGISTER ----------
            if ($action === 'register') {
                // Sanitize inputs
                $name     = trim($input['full_name'] ?? '');
                $email    = trim($input['email'] ?? '');
                $password = $input['password'] ?? '';

                // Basic validation
                if (!$name || !$email || !$password) {
                    throw new Exception('All fields required');
                }

                // Check for duplicate email
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception('Email already registered');
                }

                // Hash password and insert user
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
                $stmt->execute([$name, $email, $hash]);

                // Auto-login after registration
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'customer';

                echo json_encode(['status' => 'success', 'message' => 'Registered', 'user' => ['id' => $_SESSION['user_id'], 'name' => $name, 'role' => 'customer']]);
                break;
            }

            // ---------- LOGIN ----------
            if ($action === 'login') {
                $email    = trim($input['email'] ?? '');
                $password = $input['password'] ?? '';

                if (!$email || !$password) {
                    throw new Exception('Email and password required');
                }

                // Fetch user by email
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                // Verify password hash
                if (!$user || !password_verify($password, $user['password_hash'])) {
                    throw new Exception('Invalid credentials');
                }

                // Establish session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                echo json_encode(['status' => 'success', 'message' => 'Logged in', 'user' => ['id' => $user['id'], 'name' => $user['full_name'], 'role' => $user['role']]]);
                break;
            }

            throw new Exception('Unknown action');
            break;

        case 'GET':
            // ---------- LOGOUT ----------
            if ($action === 'logout') {
                session_destroy(); // Clear all session data
                echo json_encode(['status' => 'success', 'message' => 'Logged out']);
                break;
            }

            // ---------- CHECK CURRENT USER ----------
            if ($action === 'me') {
                if (!empty($_SESSION['user_id'])) {
                    // Return minimal user info for frontend auth state
                    echo json_encode(['status' => 'success', 'user' => ['id' => $_SESSION['user_id'], 'name' => $_SESSION['user_name'], 'role' => $_SESSION['user_role']]]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
                }
                break;
            }

            throw new Exception('Unknown action');
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    // Unified error handling: 400 for client errors, message in JSON
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
