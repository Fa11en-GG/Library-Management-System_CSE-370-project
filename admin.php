<?php
include 'db.php'; session_start();
if($_SESSION['role'] !== 'admin') { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head><title>Admin</title><style>
body { background: #0f172a; color: white; font-family: sans-serif; padding: 40px; }
.panel { background: #1e293b; padding: 25px; border-radius: 20px; border: 1px solid #334155; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; }
.low { color: #f43f5e; font-weight: bold; }
</style></head>
<body>
    <h1>Librarian Dashboard</h1>
    <div class="panel">
        <h3>Stock Status & Restock Alerts</h3>
        <table>
            <tr><th>Book</th><th>Stock</th><th>Alert</th></tr>
            <?php
            $stmt = $pdo->query("SELECT b.title, COUNT(i.item_id) as qty FROM books b LEFT JOIN inventory i ON b.isbn = i.isbn AND i.status = 'Available' GROUP BY b.isbn");
            while($row = $stmt->fetch()) {
                $alert = ($row['qty'] < 2) ? "<span class='low'>LOW STOCK</span>" : "OK";
                echo "<tr><td>{$row['title']}</td><td>{$row['qty']}</td><td>$alert</td></tr>";
            }
            ?>
        </table>
    </div>
    <br><a href="logout.php" style="color:#f43f5e">Logout</a>
</body></html>