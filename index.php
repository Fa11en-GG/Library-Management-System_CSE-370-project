<?php include 'db.php'; $user_id = 1; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reservations | Library Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --bg: #0f172a; --surface: #1e293b; --accent: #38bdf8; 
            --text: #f8fafc; --dim: #94a3b8; --danger: #f43f5e;
            --warning: #fbbf24; --success: #10b981;
        }
        
        body { background: var(--bg); color: var(--text); font-family: 'Inter', sans-serif; margin: 0; padding: 40px 20px; }
        .container { max-width: 1000px; margin: auto; }

        .main-heading { 
            font-family: 'Playfair Display', serif; font-size: 4.5rem; 
            text-align: center; margin-bottom: 50px; letter-spacing: 4px;
            color: var(--text); text-transform: uppercase;
        }

        .dashboard { 
            background: var(--surface); border-radius: 20px; padding: 30px; 
            margin-bottom: 30px; border: 1px solid rgba(255,255,255,0.05);
            display: flex; justify-content: space-between; align-items: flex-start;
        }
        .slot-num { font-size: 3.5rem; font-weight: 600; color: var(--accent); display: block; line-height: 1; }

        .search-container { margin-bottom: 40px; }
        #searchBar { 
            width: 100%; padding: 20px 30px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1);
            background: var(--surface); color: white; font-size: 1.1rem; outline: none;
        }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .card { 
            background: var(--surface); border-radius: 20px; padding: 25px; 
            border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; display: flex; flex-direction: column;
        }
        .card:hover { transform: translateY(-8px); border-color: var(--accent); }

        /* Genre Styling */
        .genre-label { 
            font-size: 0.65rem; font-weight: 700; text-transform: uppercase; 
            letter-spacing: 1.2px; color: var(--accent); opacity: 0.8; margin-bottom: 4px;
        }

        .btn { padding: 12px; border-radius: 12px; border: none; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-main { background: var(--accent); color: #0f172a; width: 100%; margin-top: 15px; }
        .btn-drop { background: rgba(244, 63, 94, 0.1); color: var(--danger); border: 1px solid var(--danger); font-size: 0.8rem; padding: 6px 12px; }
        .timer { font-family: monospace; color: var(--warning); font-weight: 600; margin-right: 15px; }
        .stock-tag { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px; display: block; }
    </style>
</head>
<body>

<div class="container">
    <h1 class="main-heading">RESERVATIONS</h1>

    <?php
    $my_q = $pdo->prepare("SELECT i.item_id, b.title, i.reserved_at FROM inventory i JOIN books b ON i.isbn = b.isbn WHERE i.reserved_by = ?");
    $my_q->execute([$user_id]);
    $holds = $my_q->fetchAll();
    $used = count($holds);
    ?>
    <div class="dashboard">
        <div class="active-holds">
            <h3 style="margin-top:0; color: var(--accent);">My Active Holds</h3>
            <?php foreach($holds as $h): $exp = strtotime($h['reserved_at']) + (12 * 3600); ?>
                <div style="display:flex; align-items:center; background:rgba(255,255,255,0.03); padding:12px 20px; border-radius:12px; margin-bottom:10px; border: 1px solid rgba(255,255,255,0.05);">
                    <span style="flex-grow:1; font-weight:500;"><?= htmlspecialchars($h['title']) ?></span>
                    <span class="timer" data-exp="<?= $exp ?>">--:--:--</span>
                    <button class="btn btn-drop" onclick="req('drop', <?= $h['item_id'] ?>)">Drop</button>
                </div>
            <?php endforeach; if($used == 0) echo "<p style='color:var(--dim)'>No books currently reserved.</p>"; ?>
        </div>
        <div class="slot-counter" style="text-align:center; padding-left:40px; border-left:1px solid rgba(255,255,255,0.1);">
            <span class="slot-num"><?= 3 - $used ?></span>
            <span style="font-size:0.7rem; color:var(--dim); text-transform:uppercase; letter-spacing:1px;">Slots Free</span>
        </div>
    </div>

    <div class="search-container">
        <input type="text" id="searchBar" placeholder="Search by title, author, genre or ISBN..." onkeyup="filter()">
    </div>

    <div class="grid" id="bookGrid">
        <?php
        $books = $pdo->query("SELECT b.*, 
            (SELECT COUNT(*) FROM book_ratings WHERE isbn = b.isbn AND rating_type='like') as l,
            (SELECT COUNT(*) FROM book_ratings WHERE isbn = b.isbn AND rating_type='dislike') as d,
            (SELECT COUNT(*) FROM inventory WHERE isbn = b.isbn AND reserved_by IS NULL) as stock
            FROM books b");
        
        foreach($books as $b): ?>
            <div class="card">
                <span class="stock-tag" style="color:<?= $b['stock'] > 0 ? 'var(--success)' : 'var(--danger)' ?>">
                    ● <?= $b['stock'] > 0 ? "In Stock: ".$b['stock'] : "Out of Stock" ?>
                </span>
                
                <div class="genre-label"><?= htmlspecialchars($b['book_type']) ?></div>
                
                <h3 style="margin:0 0 5px 0; font-size: 1.2rem;"><?= htmlspecialchars($b['title']) ?></h3>
                <p style="color:var(--dim); font-size:0.85rem; margin-bottom:20px;">By <?= htmlspecialchars($b['author_name']) ?></p>
                
                <div style="display:flex; gap:10px;">
                    <button class="btn rate-btn" style="background:rgba(255,255,255,0.05); color:white; flex:1; border:1px solid rgba(255,255,255,0.1);" onclick="req('rate', '<?= $b['isbn'] ?>', 'like')">👍 <?= $b['l'] ?></button>
                    <button class="btn rate-btn" style="background:rgba(255,255,255,0.05); color:white; flex:1; border:1px solid rgba(255,255,255,0.1);" onclick="req('rate', '<?= $b['isbn'] ?>', 'dislike')">👎 <?= $b['d'] ?></button>
                </div>

                <button class="btn btn-main" onclick="req('reserve', '<?= $b['isbn'] ?>')" <?= $b['stock'] == 0 ? 'disabled' : '' ?>>
                    <?= $b['stock'] > 0 ? 'Reserve Book' : 'Not Available' ?>
                </button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function req(action, id, type = '') {
    let f = new FormData();
    f.append('action', action);
    if(action === 'drop') f.append('item_id', id); else f.append('isbn', id);
    if(type) f.append('type', type);

    fetch('actions.php', { method: 'POST', body: f })
    .then(r => r.json()).then(res => {
        alert(res.msg);
        location.reload();
    });
}

function filter() {
    let q = document.getElementById('searchBar').value.toLowerCase();
    document.querySelectorAll('.card').forEach(c => {
        c.style.display = c.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
}

setInterval(() => {
    document.querySelectorAll('.timer').forEach(t => {
        let diff = t.getAttribute('data-exp') - Math.floor(Date.now() / 1000);
        if (diff <= 0) { t.innerHTML = "EXPIRED"; t.style.color = "var(--danger)"; }
        else {
            let h = Math.floor(diff / 3600), m = Math.floor((diff % 3600) / 60), s = diff % 60;
            t.innerHTML = `${h}h ${m}m ${s}s`;
        }
    });
}, 1000);
</script>
</body>
</html>