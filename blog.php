<?php

// 1. إعدادات وجلب البيانات

$json_file = 'data.json';

$posts = [];

if (file_exists($json_file)) {

    $json_data = file_get_contents($json_file);

    $posts = json_decode($json_data, true);

    

    // ترتيب المقالات حسب التاريخ (الأحدث أولاً)

    if (!empty($posts)) {

        usort($posts, function($a, $b) {

            return strtotime($b['date']) - strtotime($a['date']);

        });

    }

}

// دالة مساعدة لإنشاء اسم ملف وهمي من العنوان (للمظهر فقط)

function generateFileName($title) {

    $extensions = ['.js', '.md', '.css', '.json', '.sh', '.log'];

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $title)));

    $slug = substr($slug, 0, 15); // تقصير الاسم

    return $slug . $extensions[array_rand($extensions)];

}

// مصفوفة لنصوص الأزرار العشوائية

$btn_labels = ['EXECUTE', 'READ_FILE', 'VIEW_SOURCE', 'OPEN_LOG', 'RUN_SCRIPT', 'INIT_SEQ'];

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DEV.CORE | System Logs</title>

    

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=VT323&display=swap" rel="stylesheet">

    <style>

        /* --- VARIABLES --- */

        :root {

            --bg-color: #020514;

            --primary-blue: #0011ff;

            --accent-color: #ccff00;

            --text-white: #f0f0f0;

            --border-width: 2px;

        }

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

            cursor: none;

        }

        body {

            background-color: var(--bg-color);

            color: var(--text-white);

            font-family: 'Space Grotesk', sans-serif;

            overflow-x: hidden;

        }

        /* --- SHARED STYLES --- */

        .cursor {

            width: 20px; height: 20px;

            border: 2px solid var(--accent-color);

            position: fixed; pointer-events: none; z-index: 9999;

            transform: translate(-50%, -50%);

            transition: width 0.2s, height 0.2s;

            mix-blend-mode: difference;

        }

        .cursor.hovered { background: var(--accent-color); width: 50px; height: 50px; opacity: 0.5; }

        .funky-text { font-family: 'VT323', monospace; color: var(--accent-color); }

        header {

            padding: 20px 0;

            position: fixed; width: 100%; top: 0; z-index: 100;

            background: rgba(2, 5, 20, 0.9); backdrop-filter: blur(5px);

            border-bottom: 1px solid rgba(255,255,255,0.1);

        }

        

        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }

        

        nav { display: flex; justify-content: space-between; align-items: center; }

        .logo-text { font-size: 1.5rem; font-weight: bold; letter-spacing: -1px; }

        .logo-text a { text-decoration: none; color: var(--text-white); }

        .logo-text span { color: var(--primary-blue); }

        

        nav ul { display: flex; gap: 30px; list-style: none; }

        nav a { text-decoration: none; color: var(--text-white); font-weight: bold; font-size: 1.1rem; transition: 0.3s; }

        nav a:hover, nav a.active { color: var(--accent-color); text-decoration: line-through; }

        /* --- BLOG SPECIFIC HEADER --- */

        .blog-hero {

            padding-top: 150px; padding-bottom: 50px;

            border-bottom: 3px solid var(--primary-blue);

            margin-bottom: 50px; position: relative;

        }

        .blog-title {

            font-size: clamp(3rem, 6vw, 5rem); line-height: 1;

            margin-bottom: 20px; text-transform: uppercase;

        }

        

        .blog-title span { -webkit-text-stroke: 2px var(--text-white); color: transparent; }

        .search-terminal {

            background: #000; border: 1px solid var(--accent-color); padding: 15px;

            font-family: 'VT323'; font-size: 1.5rem; display: flex; align-items: center;

            max-width: 600px; box-shadow: 5px 5px 0 var(--primary-blue);

        }

        .search-input {

            background: transparent; border: none; color: var(--accent-color);

            width: 100%; font-family: 'VT323'; font-size: 1.5rem; outline: none; margin-left: 10px;

        }

        /* --- LAYOUT GRID --- */

        .content-grid {

            display: grid; grid-template-columns: 250px 1fr; gap: 50px; padding-bottom: 100px;

        }

        /* --- SIDEBAR --- */

        .sidebar h3 {

            color: var(--primary-blue); margin-bottom: 20px;

            font-family: 'VT323'; font-size: 1.8rem;

            border-bottom: 1px dashed var(--text-white); padding-bottom: 10px;

        }

        .category-list { list-style: none; }

        .category-list li { margin-bottom: 15px; }

        

        .category-btn {

            background: transparent; border: 1px solid rgba(255,255,255,0.2);

            color: var(--text-white); padding: 8px 15px; display: block; width: 100%;

            text-align: left; transition: 0.3s; font-family: 'Space Grotesk'; position: relative;

        }

        .category-btn:hover, .category-btn.active {

            background: var(--primary-blue); border-color: var(--primary-blue); padding-left: 25px;

        }

        .category-btn:hover::before {

            content: '>'; position: absolute; left: 10px; color: var(--accent-color);

        }

        /* --- POST CARDS --- */

        .posts-container {

            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;

        }

        .post-card {

            background: rgba(255,255,255,0.02); border: 2px solid var(--text-white);

            position: relative; transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);

            display: flex; flex-direction: column; height: 100%;

        }

        .post-card:hover {

            transform: translate(-5px, -5px); box-shadow: 8px 8px 0px var(--accent-color);

            border-color: var(--accent-color);

        }

        .post-header {

            background: var(--primary-blue); padding: 10px 15px;

            display: flex; justify-content: space-between;

            font-family: 'VT323'; font-size: 1.1rem; border-bottom: 2px solid var(--text-white);

        }

        .post-content {

            padding: 25px; flex-grow: 1; display: flex; flex-direction: column;

        }

        .post-tags { margin-bottom: 15px; }

        .tag {

            font-size: 0.8rem; color: var(--bg-color); background: var(--accent-color);

            padding: 2px 8px; font-weight: bold; text-transform: uppercase; margin-right: 5px;

        }

        .post-title { font-size: 1.8rem; margin-bottom: 15px; line-height: 1.2; }

        .post-excerpt {

            font-size: 1rem; color: rgba(255,255,255,0.7); margin-bottom: 20px;

            font-family: 'Courier New', monospace;

        }

        .read-btn {

            margin-top: auto; align-self: flex-start; color: var(--primary-blue);

            font-weight: bold; text-transform: uppercase; text-decoration: none;

            font-family: 'VT323'; font-size: 1.3rem; display: flex; align-items: center; gap: 10px;

        }

        .read-btn::after { content: '->'; transition: 0.3s; }

        .post-card:hover .read-btn::after { transform: translateX(5px); color: var(--accent-color); }

   
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
    }.post-card:hover .read-btn { color: var(--accent-color); }

        /* --- FEATURED POST (Wide) --- */

        .post-card.featured { grid-column: 1 / -1; border-color: var(--primary-blue); }

        .post-card.featured:hover { box-shadow: 8px 8px 0px var(--primary-blue); }

        @media (max-width: 768px) {

            .content-grid { grid-template-columns: 1fr; }

            .sidebar { margin-bottom: 40px; }

            .category-list { display: flex; flex-wrap: wrap; gap: 10px; }

            .category-btn { width: auto; }

        }

        

        .empty-msg { grid-column: 1/-1; padding: 20px; border: 1px dashed var(--accent-color); color: var(--accent-color); font-family: 'VT323'; font-size: 1.5rem; text-align: center; }

    </style>

</head>

<body>

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
            </ul>>

            </nav>

        </div>

    </header>

        <script>
    function toggleMenu() {
      var navLinks = document.getElementById('nav-links');
      navLinks.classList.toggle('show');
    }
    </script>

    <div class="container">

        <!-- HERO SECTION -->

        <section class="blog-hero">

            <h1 class="blog-title">SYSTEM <span style="color:var(--accent-color)">KNOWLEDGE</span><br><span>DATABASE</span></h1>

            

            <div class="search-terminal">

                <span style="color: var(--accent-color)">root@user:~$</span>

                <input type="text" class="search-input" placeholder="grep 'topic'..." id="search-input" autofocus>

            </div>

        </section>

        <!-- MAIN CONTENT GRID -->

        <div class="content-grid">

            

            <!-- SIDEBAR -->

            <aside class="sidebar">

                <h3>> DIRECTORIES</h3>

                <ul class="category-list">

                    <li><button class="category-btn active">./All_Posts</button></li>

                    <li><button class="category-btn">./Frontend_Dev</button></li>

                    <li><button class="category-btn">./Performance</button></li>

                    <li><button class="category-btn">./System_Arch</button></li>

                    <li><button class="category-btn">./Career_Hacks</button></li>

                </ul>

                <div style="margin-top: 50px; padding: 20px; border: 1px solid var(--primary-blue); background: rgba(0,17,255,0.1);">

                    <p class="funky-text">DAILY TIP:</p>

                    <p style="font-size: 0.9rem; margin-top: 10px;">Always meaningful variable names. `const x` is not a variable, it's a mystery.</p>

                </div>

            </aside>

            <!-- POSTS AREA -->

            <div class="posts-container">

                <?php if (empty($posts)): ?>

                    <div class="empty-msg">

                        > NO LOGS FOUND. SYSTEM IS EMPTY.

                    </div>

                <?php else: ?>

                    <!-- LOOP: Featured Post (The First One) -->

                    <?php 

                        $featured = $posts[0]; 

                        $f_title = htmlspecialchars($featured['title']);

                        $f_id = $featured['id'];

                        $f_tags = explode(',', $featured['tags']);

                        $f_excerpt = mb_substr(strip_tags($featured['content']), 0, 200) . '...';

                        $f_file = generateFileName($f_title);

                    ?>

                    

                    <article class="post-card featured">

                        <div class="post-header">

                            <span>FILE: <?php echo $f_file; ?></span>

                            <span>SIZE: 4KB</span>

                        </div>

                        <div class="post-content">

                            <div class="post-tags">

                                <?php foreach($f_tags as $tag): ?>

                                    <span class="tag"><?php echo trim($tag); ?></span>

                                <?php endforeach; ?>

                            </div>

                            <h2 class="post-title"><?php echo $f_title; ?></h2>

                            <p class="post-excerpt"><?php echo $f_excerpt; ?></p>

                            <a href="single.php?id=<?php echo $f_id; ?>" class="read-btn">READ_FILE</a>

                        </div>

                    </article>

                    <!-- LOOP: Other Posts (Starting from index 1) -->

                    <?php for($i = 1; $i < count($posts); $i++): 

                        $p = $posts[$i];

                        $title = htmlspecialchars($p['title']);

                        $id = $p['id'];

                        $tags = explode(',', $p['tags']);

                        $excerpt = mb_substr(strip_tags($p['content']), 0, 100) . '...';

                        $file = generateFileName($title);

                        // اختيار نص زر عشوائي

                        $btn_text = $btn_labels[array_rand($btn_labels)];

                    ?>

                        <article class="post-card">

                            <div class="post-header">

                                <span>FILE: <?php echo $file; ?></span>

                                <span><?php echo date('h:i A', strtotime($p['date'])); ?></span>

                            </div>

                            <div class="post-content">

                                <div class="post-tags">

                                    <?php foreach($tags as $tag): ?>

                                        <span class="tag"><?php echo trim($tag); ?></span>

                                    <?php endforeach; ?>

                                </div>

                                <h2 class="post-title"><?php echo $title; ?></h2>

                                <p class="post-excerpt"><?php echo $excerpt; ?></p>

                                <a href="single.php?id=<?php echo $id; ?>" class="read-btn"><?php echo $btn_text; ?></a>

                            </div>

                        </article>

                    <?php endfor; ?>

                <?php endif; ?>

            </div>

        </div>

    </div>

    <script>

        // --- CUSTOM CURSOR ---

        const cursor = document.querySelector('.cursor');

        document.addEventListener('mousemove', (e) => {

            cursor.style.left = e.clientX + 'px';

            cursor.style.top = e.clientY + 'px';

        });

        const hoverElements = document.querySelectorAll('a, button, .post-card, input');

        hoverElements.forEach(el => {

            el.addEventListener('mouseenter', () => cursor.classList.add('hovered'));

            el.addEventListener('mouseleave', () => cursor.classList.remove('hovered'));

        });

        // --- SEARCH FILTER ---

        const searchInput = document.getElementById('search-input');

        const cards = document.querySelectorAll('.post-card');

        if(searchInput) {

            searchInput.addEventListener('keyup', (e) => {

                const term = e.target.value.toLowerCase();

                cards.forEach(card => {

                    const title = card.querySelector('.post-title').textContent.toLowerCase();

                    const tags = card.querySelector('.post-tags').textContent.toLowerCase();

                    if(title.includes(term) || tags.includes(term)) {

                        card.style.display = 'flex';

                    } else {

                        card.style.display = 'none';

                    }

                });

            });

        }

        // --- SIDEBAR FILTER ANIMATION ---

        const categoryBtns = document.querySelectorAll('.category-btn');

        categoryBtns.forEach(btn => {

            btn.addEventListener('click', () => {

                categoryBtns.forEach(b => b.classList.remove('active'));

                btn.classList.add('active');

                const container = document.querySelector('.posts-container');

                container.style.opacity = '0';

                setTimeout(() => { container.style.opacity = '1'; }, 200);

            });

        });

    </script>

</body>

</html>
