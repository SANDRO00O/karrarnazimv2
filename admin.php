<?php
session_start();
$password = "123"; // <--- غير كلمة المرور هنا

// 1. تسجيل الدخول
if (isset($_POST['login'])) {
    if ($_POST['pass'] === $password) {
        $_SESSION['admin'] = true;
    } else {
        $error = "ACCESS DENIED: WRONG PASSWORD";
    }
}

// 2. تسجيل الخروج
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit();
}

// 3. حفظ المقال
if (isset($_POST['publish']) && isset($_SESSION['admin'])) {
    $json_file = 'data.json';
    $current_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

    // رفع الصورة
    $image_path = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $filename;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    // إنشاء المقال
    $new_post = [
        'id' => uniqid(),
        'title' => $_POST['title'],
        'tags' => $_POST['tags'],
        'content' => $_POST['content'], // سيحتوي على HTML من المحرر
        'image' => $image_path,
        'date' => date("Y-m-d H:i")
    ];

    array_unshift($current_data, $new_post); // إضافة في البداية
    file_put_contents($json_file, json_encode($current_data, JSON_PRETTY_PRINT));
    $success = "SYSTEM UPDATE: POST DEPLOYED SUCCESSFULLY.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN // TERMINAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;700&family=VT323&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #020514; --blue: #0011ff; --lime: #ccff00; --white: #f0f0f0; }
        * { box-sizing: border-box; outline: none; }
        body { background: var(--bg); color: var(--white); font-family: 'VT323', monospace; display: flex; justify-content: center; min-height: 100vh; padding: 50px 20px; }
        
        .terminal { width: 100%; max-width: 800px; border: 2px solid var(--blue); background: rgba(0,0,0,0.5); padding: 20px; box-shadow: 0 0 20px rgba(0,17,255,0.2); }
        h1 { color: var(--lime); border-bottom: 1px dashed var(--white); padding-bottom: 10px; margin-bottom: 20px; }
        
        input, textarea { width: 100%; background: #000; border: 1px solid #333; color: var(--lime); padding: 15px; font-family: 'VT323'; font-size: 1.2rem; margin-bottom: 20px; transition: 0.3s; }
        input:focus, textarea:focus { border-color: var(--blue); box-shadow: 0 0 10px var(--blue); }
        
        .btn { background: transparent; border: 2px solid var(--lime); color: var(--lime); padding: 10px 30px; font-size: 1.5rem; cursor: pointer; font-family: 'VT323'; text-transform: uppercase; }
        .btn:hover { background: var(--lime); color: #000; }
        
        /* Custom Editor Toolbar */
        .toolbar { display: flex; gap: 10px; margin-bottom: 5px; background: #111; padding: 5px; border: 1px solid #333; }
        .tool-btn { background: #222; border: 1px solid #444; color: var(--white); padding: 5px 10px; cursor: pointer; font-family: 'Space Grotesk'; font-size: 0.9rem; }
        .tool-btn:hover { border-color: var(--lime); color: var(--lime); }

        .preview-area { margin-top: 10px; border-top: 1px dashed #333; padding-top: 10px; color: #aaa; font-size: 0.9rem; }
    </style>
</head>
<body>

    <div class="terminal">
        <h1>> ADMIN_ACCESS_PANEL</h1>

        <?php if (!isset($_SESSION['admin'])): ?>
            <!-- LOGIN FORM -->
            <form method="POST">
                <p style="color:red;"><?php echo $error ?? ''; ?></p>
                <label>> ENTER_PASSWORD:</label>
                <input type="password" name="pass" autofocus placeholder="******">
                <button type="submit" name="login" class="btn">AUTHENTICATE</button>
            </form>
        
        <?php else: ?>
            <!-- DASHBOARD -->
            <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
                <span>STATUS: <span style="color:var(--lime)">LOGGED_IN</span></span>
                <a href="?logout" style="color:red; text-decoration:none;">[ TERMINATE_SESSION ]</a>
            </div>

            <?php if (isset($success)) echo "<p style='color:var(--lime); border:1px solid var(--lime); padding:10px; margin-bottom:20px;'>$success</p>"; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>> POST_TITLE:</label>
                <input type="text" name="title" required placeholder="e.g. Optimizing React Hooks">

                <label>> TAGS (Comma separated):</label>
                <input type="text" name="tags" placeholder="REACT, PERFORMANCE, JS">

                <label>> FEATURED_IMAGE:</label>
                <input type="file" name="image" accept="image/*" style="padding: 5px;">

                <label>> CONTENT_SOURCE:</label>
                
                <!-- Custom Editor Controls -->
                <div class="toolbar">
                    <button type="button" class="tool-btn" onclick="insertTag('b')">BOLD</button>
                    <button type="button" class="tool-btn" onclick="insertTag('i')">ITALIC</button>
                    <button type="button" class="tool-btn" onclick="insertTag('code')">CODE_BLOCK</button>
                    <button type="button" class="tool-btn" onclick="insertTag('h2')">HEADING</button>
                    <button type="button" class="tool-btn" onclick="insertTag('br')">LINE_BREAK</button>
                </div>
                
                <!-- The Editor Textarea -->
                <textarea name="content" id="editor" rows="15" required placeholder="Write your code logic here..."></textarea>

                <button type="submit" name="publish" class="btn" style="width:100%; margin-top:10px;">[ EXECUTE_PUBLISH ]</button>
            </form>
            
            <div style="margin-top: 30px; text-align: center;">
                <a href="blog.php" target="_blank" style="color: var(--blue); text-decoration: none;">> VIEW_LIVE_SITE</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // سكربت بسيط لمحرر النصوص
        function insertTag(type) {
            const textarea = document.getElementById('editor');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);
            
            let replacement = '';
            
            if (type === 'b') replacement = `<b>${selectedText}</b>`;
            else if (type === 'i') replacement = `<i>${selectedText}</i>`;
            else if (type === 'code') replacement = `<pre style="background:#111; padding:10px; border:1px solid #333; color:#ccff00;"><code>${selectedText}</code></pre>`;
            else if (type === 'h2') replacement = `<h2 style="color:#0011ff;">${selectedText}</h2>`;
            else if (type === 'br') replacement = `<br>`;
            
            // دمج النص الجديد
            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            
            // إعادة التركيز
            textarea.focus();
        }
    </script>
</body>
</html>