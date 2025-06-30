<?php
try {
    if (isset($_COOKIE['user_id'])) {
        unset($_COOKIE['user_id']);
        setcookie('user_id', '', time() - 3600, '/'); // empty value and old timestamp
    }
    echo json_encode(['status' => 'success', 'message' => 'Logged out']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Session error: ' . $e->getMessage()]);
    exit;
}
?>