<?php
// 1. جلب معرف المقال من الرابط
$id = $_GET['id'] ?? null;
$post = null;

// 2. البحث عن المقال داخل ملف JSON
if ($id && file_exists('data.json')) {
    $json_data = file_get_contents('data.json');
    $posts = json_decode($json_data, true);
    
    foreach ($posts as $p) {
        if ($p['id'] === $id) {
            $post = $p;
            break;
        }
    }
}

// 3. إذا لم يتم العثور على المقال، ارجع للمدونة
if (!$post) {
    header("Location: blog.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['title']); ?> | DEV.CORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=VT323&display=swap" rel="stylesheet">
    
    <style>
        /* --- CORE VARIABLES --- */
        :root {
            --bg-color: #020514;
            --primary-blue: #0011ff;
            --accent-color: #ccff00;
            --text-white: #f0f0f0;
            --code-bg: #050a1f;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; cursor: none; }

        body {
            background-color: var(--bg-color);
            color: var(--text-white);
            font-family: 'Space Grotesk', sans-serif;
            line-height: 1.8;
            overflow-x: hidden;
        }

        /* --- READING PROGRESS BAR --- */
        #progress-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: transparent;
            z-index: 1000;
        }
        #progress-bar {
            height: 100%;
            background: var(--accent-color);
            width: 0%;
            box-shadow: 0 0 10px var(--accent-color);
        }

        /* --- CUSTOM CURSOR --- */
        .cursor {
            width: 20px; height: 20px;
            border: 2px solid var(--accent-color);
            position: fixed; pointer-events: none; z-index: 9999;
            transform: translate(-50%, -50%);
            transition: width 0.2s, height 0.2s;
            mix-blend-mode: difference;
        }
        .cursor.hovered { background: var(--accent-color); width: 50px; height: 50px; opacity: 0.5; }

        /* --- NAVIGATION --- */
        header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(2, 5, 20, 0.95);
            backdrop-filter: blur(5px);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .container { max-width: 900px; margin: 0 auto; padding: 0 20px; }
        nav { display: flex; justify-content: space-between; align-items: center; }
        .back-btn {
            color: var(--primary-blue);
            text-decoration: none;
            font-family: 'VT323';
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .back-btn:hover { color: var(--accent-color); }

        /* --- ARTICLE HEADER --- */
        .article-header { margin-top: 60px; margin-bottom: 40px; border-left: 4px solid var(--primary-blue); padding-left: 20px; }
        .meta-data { font-family: 'VT323'; color: var(--accent-color); font-size: 1.1rem; margin-bottom: 10px; display: flex; gap: 20px; }
        h1 { font-size: clamp(2.5rem, 5vw, 4rem); line-height: 1.1; text-transform: uppercase; margin-bottom: 20px; }
        .tags span { display: inline-block; background: rgba(255,255,255,0.1); padding: 2px 10px; font-size: 0.8rem; margin-right: 10px; font-weight: bold; }

        /* --- FEATURED IMAGE --- */
        .featured-image-container {
            width: 100%;
            max-height: 500px;
            overflow: hidden;
            margin-bottom: 60px;
            border: 2px solid var(--text-white);
            position: relative;
        }
        .featured-image { width: 100%; height: 100%; object-fit: cover; }
        .featured-image-container::after {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, transparent 50%, rgba(0,17,255,0.2));
            pointer-events: none;
        }

        /* --- CONTENT TYPOGRAPHY --- */
        .content-area { font-size: 1.1rem; color: #ddd; }
        .content-area p { margin-bottom: 20px; }
        .content-area h2 { color: var(--primary-blue); font-size: 2rem; margin-top: 40px; margin-bottom: 20px; font-family: 'VT323'; border-bottom: 1px dashed #333; display: inline-block; }
        .content-area b { color: var(--accent-color); font-weight: 700; }
        .content-area i { color: #aaa; }
        .content-area ul { margin-left: 20px; margin-bottom: 20px; }
        .content-area li { margin-bottom: 10px; }

        /* --- CODE BLOCKS (From Custom Editor) --- */
        .content-area pre {
            background: var(--code-bg);
            border: 1px solid var(--primary-blue);
            padding: 20px;
            overflow-x: auto;
            margin: 30px 0;
            position: relative;
            box-shadow: 5px 5px 0px rgba(0,17,255,0.2);
        }
        .content-area pre::before {
            content: 'root@script:~# code_snippet';
            display: block;
            font-family: 'VT323';
            color: var(--accent-color);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .content-area code {
            font-family: 'VT323', monospace;
            font-size: 1.2rem;
            color: #fff;
        }

        /* --- FOOTER AREA --- */
     /* Navigation styles */
nav ul {
    list-style: none;
    display: flex;
    gap: 20px;
}
nav a {
    color: var(--white);
    text-decoration: none;
}
.burger {
    display: none;
    font-size: 24px;
    cursor: pointer;
    color: var(--white);
}
@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
        display: none;
        width: 100%;
    }
    nav ul.show {
        display: flex;
    }
    .burger {
        display: block;
    }
}
   .post-footer { margin-top: 80px; padding-top: 40px; border-top: 1px solid #333; text-align: center; }
        .glitch-btn {
            display: inline-block; padding: 15px 40px;
            border: 2px solid var(--accent-color);
            color: var(--accent-color); font-family: 'VT323'; font-size: 1.5rem;
            text-decoration: none; transition: 0.3s;
        }
        .glitch-btn:hover { background: var(--accent-color); color: var(--bg-color); box-shadow: 0 0 20px var(--accent-color); }

        @media (max-width: 768px) {
            h1 { font-size: 2rem; }
            .container { padding: 0 15px; }
        }
    </style>
</head>
<body>

    <!-- PROGRESS BAR -->
    <div id="progress-container"><div id="progress-bar"></div></div>

    <!-- CURSOR -->
    <div class="cursor"></div>

    <header>
    <div class="container">
        <nav>
            <div class="logo-text"><a href="index.html">DEV<span>.CORE</span></a></div>
            <div class="burger" onclick="toggleMenu()">&#x2630;</div>
            <ul id="nav-links">
                <li><a href="index.html">HOME</a></li>
                <li><a href="blog.php" class="active">LOGS</a></li>
                <li><a href="admin.php">ADMIN</a></li>
            </ul>
        </nav>
    </div>
</header>
<script>
function toggleMenu() {
    var navLinks = document.getElementById('nav-links');
    navLinks.classList.toggle('show');
}


    <main class="container">
        <!-- HEADER -->
        <article class="article-header">
            <div class="meta-data">
                <span>DATE: <?php echo $post['date']; ?></span>
                <span>ID: <?php echo substr($post['id'], 0, 6); ?></span>
            </div>
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="tags">
                <?php 
                    $tags = explode(',', $post['tags']);
                    foreach($tags as $tag) echo "<span>".trim($tag)."</span>";
                ?>
            </div>
        </article>

        <!-- IMAGE -->
        <?php if (!empty($post['image'])): ?>
        <div class="featured-image-container">
            <img src="<?php echo $post['image']; ?>" alt="System Visual" class="featured-image">
        </div>
        <?php endif; ?>

        <!-- CONTENT -->
        <div class="content-area">
            <?php 
                // طباعة المحتوى كما هو (لأنه يحتوي على HTML من المحرر)
                // ملاحظة: في بيئة حقيقية يفضل استخدام HTML Purifier للأمان
                echo $post['content']; 
            ?>
        </div>

        <!-- FOOTER -->
        <div class="post-footer">
            <p style="margin-bottom:20px; font-family:'VT323'; color:#666;">END OF FILE // EOF</p>
            <a href="blog.php" class="glitch-btn">EXECUTE_EXIT</a>
        </div>
    </main>

    <script>
        // --- CURSOR LOGIC ---
        const cursor = document.querySelector('.cursor');
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX + 'px';
            cursor.style.top = e.clientY + 'px';
        });
        document.querySelectorAll('a').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hovered'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hovered'));
        });

        // --- READING PROGRESS BAR ---
        window.onscroll = function() {
            let winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            let height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            let scrolled = (winScroll / height) * 100;
            document.getElementById("progress-bar").style.width = scrolled + "%";
        };
    </script>
</body>
</html>
