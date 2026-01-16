<?php
// Simple authentication system
session_start();

function isLoggedIn() {
    return isset($_SESSION['employee_id']) && isset($_SESSION['employee_name']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getEmployeeName() {
    return $_SESSION['employee_name'] ?? '';
}

function getEmployeeRole() {
    return $_SESSION['role'] ?? 'employee';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function login($username, $password, $db) {
    $stmt = $db->prepare("SELECT id, employee_name, role, password FROM employees WHERE username = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee && password_verify($password, $employee['password'])) {
        $_SESSION['employee_id'] = $employee['id'];
        $_SESSION['employee_name'] = $employee['employee_name'];
        $_SESSION['role'] = $employee['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
