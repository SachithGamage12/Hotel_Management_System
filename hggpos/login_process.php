<?php


session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: index.php");
        exit();
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT username, password FROM hgg_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password (assuming password is hashed in the database)
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['success'] = "Login successful! Welcome back!";
            header("Location: dashboard.php"); // Redirect to dashboard or desired page
            exit();
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $_SESSION['error'] = "Username not found.";
    }
    
    $stmt->close();
    header("Location: index.php");
    exit();
}

$conn->close();
?>