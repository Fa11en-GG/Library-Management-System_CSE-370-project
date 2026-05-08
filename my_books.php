<?php 
include 'db.php'; session_start(); 
if(!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Books | Library</title>
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --accent: #38bdf8; --text: #f8fafc; --danger: #f43f5e; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        .nav { display: flex; gap: 20px; margin-bottom: 30px; }
        .nav a { color: var(--accent); text-decoration: none; font-weight: bold; }
        
        .loan-card { background: var(--surface); padding: 20px; border-radius: 15px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; border: 1px solid #334155; }
        .overdue { border-color: var(--danger); background: rgba(244, 63, 94, 0.05); }
        .btn-return { background: var(--accent); color: #0f172a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .date-label { font-size: 0.8rem; color: #94a3b8; }
        #toast { position: fixed; top: 20px; right: 20px; background: var(--accent); color: #000; padding: 15px; border-radius: 10px; display: none; z-index: 1000; }
    </style>
</head>
<body>
<div id="toast"></div>
<div class="container">
    <div class="nav">
        <a href="index.php">← Back to Catalog</a>
        <span style="margin-left:auto">User: <?= $user ?></span>
    </div>

    <h2>My Active Loans</h2>
    
    <?php
    $stmt = $pdo->prepare("
        SELECT l.loan_id, b.title, l.due_date, 
        DATEDIFF(l.due_date, CURDATE()) as days_left
        FROM loans l
        JOIN inventory i ON l.item_id = i.item_id
        JOIN books b ON i.isbn = b.isbn
        WHERE l.borrower_phone = ? AND l.status = 'checked_out'
    ");
    $stmt->execute([$user]);
    $loans = $stmt->fetchAll();

    if (count($loans) > 0) {
        foreach($loans as $loan): 
            $is_overdue = ($loan['days_left'] < 0);
        ?>
            <div class="loan-card <?= $is_overdue ? 'overdue' : '' ?>">
                <div>
                    <h3 style="margin:0"><?= htmlspecialchars($loan['title']) ?></h3>
                    <span class="date-label">Due Date: <?= $loan['due_date'] ?></span>
                    <?php if($is_overdue): ?>
                        <span style="color:var(--danger); font-weight:bold; margin-left:10px;">(OVERDUE)</span>
                    <?php else: ?>
                        <span style="color:#10b981; margin-left:10px;">(<?= $loan['days_left'] ?> days left)</span>
                    <?php endif; ?>
                </div>
                <button class="btn-return" onclick="returnBook(<?= $loan['loan_id'] ?>)">Return Book</button>
            </div>
        <?php endforeach;
    } else {
        echo "<p style='color:#64748b'>You don't have any borrowed books right now.</p>";
    }
    ?>
</div>

<script>
function returnBook(loanId) {
    if(!confirm("Are you sure you want to return this book?")) return;
    
    let f = new FormData();
    f.append('action', 'return');
    f.append('loan_id', loanId);

    fetch('actions.php', { method: 'POST', body: f })
    .then(r => r.json())
    .then(res => {
        const t = document.getElementById('toast');
        t.innerText = res.msg;
        t.style.display = 'block';
        setTimeout(() => location.reload(), 1500);
    });
}
</script>
</body>
</html>