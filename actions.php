<?php
include 'db.php';
session_start();

// Ensure user is logged in
if(!isset($_SESSION['user'])) {
    echo json_encode(["msg" => "Error: Not logged in"]);
    exit;
}

$user = $_SESSION['user']; 
$action = $_POST['action'] ?? ''; 
$isbn = $_POST['isbn'] ?? '';

header('Content-Type: application/json');

try {
    if ($action == 'borrow' || $action == 'reserve') {
        $status_map = ['borrow' => 'Borrowed', 'reserve' => 'Reserved'];
        $loan_map = ['borrow' => 'checked_out', 'reserve' => 'reserved'];
        
        // 1. Check for available copy
        $stmt = $pdo->prepare("SELECT item_id FROM inventory WHERE isbn = ? AND status = 'Available' LIMIT 1");
        $stmt->execute([$isbn]);
        $item = $stmt->fetch();

        if ($item) {
            $pdo->beginTransaction();
            // 2. Record Loan/Reservation
            $stmt = $pdo->prepare("INSERT INTO loans (item_id, borrower_phone, checkout_date, due_date, status) VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), ?)");
            $stmt->execute([$item['item_id'], $user, $loan_map[$action]]); 
            
            // 3. Update Inventory
            $pdo->prepare("UPDATE inventory SET status = ? WHERE item_id = ?")->execute([$status_map[$action], $item['item_id']]);
            
            $pdo->commit();
            echo json_encode(["msg" => "Success: Book " . $action . "ed!"]);
        } else {
            echo json_encode(["msg" => "Error: No copies available"]);
        }
    }

    if ($action == 'rate') {
        $type = $_POST['rating_type'];
        $stmt = $pdo->prepare("INSERT INTO book_ratings (phone, isbn, rating_type) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE rating_type = ?");
        $stmt->execute([$user, $isbn, $type, $type]);
        echo json_encode(["msg" => "Rating saved"]);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(["msg" => "DB Error: " . $e->getMessage()]);
}
?>