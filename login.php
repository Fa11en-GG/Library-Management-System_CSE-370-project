<?php
include 'db.php'; session_start(); $msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone']; $pass = $_POST['password'];
    if (isset($_POST['register'])) {
        try {
            $pdo->prepare("INSERT INTO users (phone, password, role) VALUES (?, ?, 'user')")->execute([$phone, $pass]);
            $msg = "<span style='color:#10b981'>Registration successful! Please login.</span>";
        } catch (Exception $e) { $msg = "<span style='color:#f43f5e'>Phone number already exists.</span>"; }
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? AND password = ?");
        $stmt->execute([$phone, $pass]);
        $u = $stmt->fetch();
        if ($u) {
            $_SESSION['user'] = $u['phone']; $_SESSION['role'] = $u['role'];
            header("Location: " . ($u['role'] == 'admin' ? 'admin.php' : 'index.php')); exit();
        } else { $msg = "<span style='color:#f43f5e'>Invalid credentials.</span>"; }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Access</title><style>
body { background: #0f172a; color: white; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
.card { background: #1e293b; padding: 40px; border-radius: 20px; width: 320px; text-align: center; }
input { width: 100%; padding: 12px; margin-bottom: 10px; border-radius: 8px; border: 1px solid #334155; background: #0f172a; color: white; box-sizing: border-box; }
.btn { width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; background: #38bdf8; color: #0f172a; margin-top: 10px; }
.toggle { font-size: 0.8rem; color: #94a3b8; cursor: pointer; display: block; margin-top: 15px; }
</style></head>
<body>
<div class="card">
    <h2 id="t">Login</h2>
    <p><?= $msg ?></p>
    <form method="POST">
        <input type="text" name="phone" placeholder="Phone Number" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login" id="b" class="btn">Login</button>
        <span class="toggle" onclick="toggle()">Create an account</span>
    </form>
    <p style="font-size:0.6rem; color:#475569; margin-top:20px;">Admin: 0123456789 / admin123<br>User: 1112223333 / pass123</p>
</div>
<script>
function toggle() {
    const t = document.getElementById('t'), b = document.getElementById('b');
    if(t.innerText === "Login") { t.innerText = "Register"; b.innerText = "Register"; b.name = "register"; }
    else { t.innerText = "Login"; b.innerText = "Login"; b.name = "login"; }
}
</script>
</body></html>