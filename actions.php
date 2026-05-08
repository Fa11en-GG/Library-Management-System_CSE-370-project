<?php
include 'db.php';
$user_id = 1; // Simulated active user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $response = ["status" => "error", "msg" => "Request failed"];

    if ($action == 'reserve') {
        $isbn = $_POST['isbn'];
        $total = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE reserved_by = ?");
        $total->execute([$user_id]);
        
        $duplicate = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE reserved_by = ? AND isbn = ?");
        $duplicate->execute([$user_id, $isbn]);

        if ($total->fetchColumn() >= 3) {
            $response['msg'] = "Limit reached: Max 3 reservations allowed.";
        } elseif ($duplicate->fetchColumn() > 0) {
            $response['msg'] = "You already have a copy of this book.";
        } else {
            $stmt = $pdo->prepare("UPDATE inventory SET reserved_by = ?, reserved_at = NOW() WHERE isbn = ? AND reserved_by IS NULL LIMIT 1");
            $stmt->execute([$user_id, $isbn]);
            if ($stmt->rowCount() > 0) $response = ["status" => "success", "msg" => "Reserved for 12 hours!"];
            else $response['msg'] = "Sorry, no copies currently available.";
        }
    }

    if ($action == 'drop') {
        $item_id = $_POST['item_id'];
        $pdo->prepare("UPDATE inventory SET reserved_by = NULL, reserved_at = NULL WHERE item_id = ?")->execute([$item_id]);
        $response = ["status" => "success", "msg" => "Reservation released."];
    }

    if ($action == 'rate') {
        $isbn = $_POST['isbn'];
        $type = $_POST['type'];
        $pdo->prepare("REPLACE INTO book_ratings (user_id, isbn, rating_type) VALUES (?, ?, ?)")->execute([$user_id, $isbn, $type]);
        $response = ["status" => "success", "msg" => "Rating updated."];
    }

    echo json_encode($response);
    exit;
}