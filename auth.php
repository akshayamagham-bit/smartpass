<?php
/**
 * Authentication helpers - session management, role checks, login guards.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

function is_student_logged_in(): bool {
    return isset($_SESSION['student_id']);
}

function is_admin_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function require_student(): void {
    if (!is_student_logged_in()) {
        header('Location: ' . base_url() . 'login.php');
        exit;
    }
}

function require_admin(): void {
    if (!is_admin_logged_in()) {
        header('Location: ' . base_url() . 'login.php');
        exit;
    }
}

function current_student(): ?array {
    if (!is_student_logged_in()) return null;
    $stmt = db()->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('i', $_SESSION['student_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
}

function current_admin(): ?array {
    if (!is_admin_logged_in()) return null;
    $stmt = db()->prepare('SELECT * FROM admins WHERE admin_id = ?');
    $stmt->bind_param('i', $_SESSION['admin_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res ? $res->fetch_assoc() : null;
}

function base_url(): string {
    // Adjust if hosted in a subfolder. Default: project root.
    return '/';
}

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
