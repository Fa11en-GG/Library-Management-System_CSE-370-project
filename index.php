<?php 
include 'db.php'; session_start(); 
if(!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }
$user = $_SESSION['user'];
$search = $_GET['search'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Portal</title>
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --accent: #38bdf8; --text: #f8fafc; --success: #10b981; }
        body { background: var(--bg); color: var(--text); font-family: sans-serif; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        .nav { display: flex; justify-content: space-between; padding: 20px; background: var(--surface); border-radius: 15px; margin-bottom: 25px; }
        .nav a { color: var(--text); text-decoration: none; margin-left: 15px; font-weight: bold; }
        .search-area { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-input { flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #334155; background: var(--surface); color: white; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .card { background: var(--surface); padding: 25px; border-radius: 20px; border: 1px solid #334155; position: relative; }
        .btn-box { display: flex; gap: 10px; margin-top: 15px; }
        .btn { flex: 1; padding: 12px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; }
        .btn-borrow { background: var(--accent); color: #0f172a; }
        .btn-reserve { background: transparent; border: 1px solid var(--accent); color: var(--accent); }
        .rate-btn { background: none; border: none; cursor: pointer; font-size: 1.3rem; filter: grayscale(1); opacity: 0.4; }
        .rate-btn.active { filter: grayscale(0); opacity: 1; transform: scale(1.2); }
    </style>
</head>
<body>

<div class="container">
    <div class="nav">
        <span>Logged in: <b style="color:var(--accent)"><?= $user ?></b></span>
        <div>
            <a href="index.php">🏠 Home</a>
            <a href="my_books.php">📚 My Books</a>
            <a href="logout.php" style="color:#f43f5e">Logout</a>
        </div>
    </div>

    <form class="search-area" method="GET">
        <input type="text" name="search" class="search-input" placeholder="Search by title or author..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" style="background:var(--accent); border:none; padding:0 25px; border-radius:8px; cursor:pointer; font-weight:bold;">Search</button>
    </form>

    <div class="grid">
        <?php
        // Unified Query with correct column names
        $sql = "SELECT b.*, 
            (SELECT COUNT(*) FROM inventory WHERE isbn = b.isbn AND status = 'Available') as stock,
            (SELECT rating_type FROM book_ratings WHERE isbn = b.isbn AND phone = ?) as my_rating
            FROM books b";
        
        if ($search) {
            $sql .= " WHERE b.title LIKE ? OR b.author_name LIKE ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user, "%$search%", "%$search%"]);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user]);
        }

        while($row = $stmt->fetch()): ?>
            <div class="card">
                <div style="margin-bottom:10px">
                    <button onclick="rate('<?= $row['isbn'] ?>', 'like')" class="rate-btn <?= $row['my_rating']=='like'?'active':'' ?>">👍</button>
                    <button onclick="rate('<?= $row['isbn'] ?>', 'dislike')" class="rate-btn <?= $row['my_rating']=='dislike'?'active':'' ?>">👎</button>
                </div>
                <h3><?= htmlspecialchars($row['title']) ?></h3>
                <p style="color:#94a3b8; font-size:0.9rem">By <?= htmlspecialchars($row['author_name']) ?></p>
                <p style="font-weight:bold; color:<?= $row['stock']>0?'#10b981':'#f43f5e' ?>"><?= $row['stock'] ?> Copies Available</p>
                
                <div class="btn-box">
                    <button class="btn btn-borrow" onclick="act('borrow', '<?= $row['isbn'] ?>')" <?= $row['stock']==0?'disabled':'' ?>>Borrow</button>
                    <button class="btn btn-reserve" onclick="act('reserve', '<?= $row['isbn'] ?>')" <?= $row['stock']==0?'disabled':'' ?>>Reserve</button>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
// Logic to handle clicks with debugging alerts
function act(action, isbn) {
    let f = new FormData(); 
    f.append('action', action); 
    f.append('isbn', isbn);

    fetch('actions.php', { method: 'POST', body: f })
    .then(r => r.text()) // Get text first to catch PHP errors
    .then(text => {
        try {
            const res = JSON.parse(text);
            alert(res.msg);
            location.reload();
        } catch(e) {
            console.error("Server Error:", text);
            alert("Check Console (F12) - The server returned an error.");
        }
    });
}

function rate(isbn, type) {
    let f = new FormData(); 
    f.append('action', 'rate'); 
    f.append('isbn', isbn); 
    f.append('rating_type', type);

    fetch('actions.php', { method: 'POST', body: f })
    .then(() => location.reload());
}
</script>
</body>
</html>