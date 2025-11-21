<?php
// قراءة البيانات من ملف JSON
$json_data = file_get_contents('data.json');
$posts = json_decode($json_data, true);

// ترتيب المقالات من الأحدث للأقدم
if ($posts) {
    usort($posts, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
} else {
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEV.CORE | System Logs</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;500;700&family=VT323&display=swap" rel="stylesheet">
    <style>
        
        /* --- VARIABLES (SAME AS HOME) --- */
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
            width: 20px;
            height: 20px;
            border: 2px solid var(--accent-color);
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: width 0.2s, height 0.2s;
            mix-blend-mode: difference;
        }
        
        .cursor.hovered {
            background: var(--accent-color);
            width: 50px;
            height: 50px;
            opacity: 0.5;
        }
        
        .funky-text {
            font-family: 'VT323', monospace;
            color: var(--accent-color);
        }
        
        header {
            padding: 20px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            background: rgba(2, 5, 20, 0.9);
            backdrop-filter: blur(5px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: -1px;
        }
        
        .logo-text span {
            color: var(--primary-blue);
        }
        
        nav ul {
            display: flex;
            gap: 30px;
            list-style: none;
        }
        
        nav a {
            text-decoration: none;
            color: var(--text-white);
            font-weight: bold;
            font-size: 1.1rem;
            transition: 0.3s;
        }
        
        nav a:hover,
        nav a.active {
            color: var(--accent-color);
            text-decoration: line-through;
        }
        
        /* --- BLOG SPECIFIC HEADER --- */
        .blog-hero {
            padding-top: 150px;
            padding-bottom: 50px;
            border-bottom: 3px solid var(--primary-blue);
            margin-bottom: 50px;
            position: relative;
        }
        
        .blog-title {
            font-size: clamp(3rem, 6vw, 5rem);
            line-height: 1;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        
        .blog-title span {
            -webkit-text-stroke: 2px var(--text-white);
            color: transparent;
        }
        
        .search-terminal {
            background: #000;
            border: 1px solid var(--accent-color);
            padding: 15px;
            font-family: 'VT323';
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            max-width: 600px;
            box-shadow: 5px 5px 0 var(--primary-blue);
        }
        
        .search-input {
            background: transparent;
            border: none;
            color: var(--accent-color);
            width: 100%;
            font-family: 'VT323';
            font-size: 1.5rem;
            outline: none;
            margin-left: 10px;
        }
        
        /* --- LAYOUT GRID --- */
        .content-grid {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 50px;
            padding-bottom: 100px;
        }
        
        /* --- SIDEBAR (FILTERS) --- */
        .sidebar h3 {
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-family: 'VT323';
            font-size: 1.8rem;
            border-bottom: 1px dashed var(--text-white);
            padding-bottom: 10px;
        }
        
        .category-list {
            list-style: none;
        }
        
        .category-list li {
            margin-bottom: 15px;
        }
        
        .category-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-white);
            padding: 8px 15px;
            display: block;
            width: 100%;
            text-align: left;
            transition: 0.3s;
            font-family: 'Space Grotesk';
            position: relative;
        }
        
        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            padding-left: 25px;
        }
        
        .category-btn:hover::before {
            content: '>';
            position: absolute;
            left: 10px;
            color: var(--accent-color);
        }
        
        /* --- POST CARDS --- */
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .post-card {
            background: rgba(255, 255, 255, 0.02);
            border: 2px solid var(--text-white);
            position: relative;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        /* Funky Hover Effect */
        .post-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 8px 8px 0px var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .post-header {
            background: var(--primary-blue);
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            font-family: 'VT323';
            font-size: 1.1rem;
            border-bottom: 2px solid var(--text-white);
        }
        
        .post-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .post-tags {
            margin-bottom: 15px;
        }
        
        .tag {
            font-size: 0.8rem;
            color: var(--bg-color);
            background: var(--accent-color);
            padding: 2px 8px;
            font-weight: bold;
            text-transform: uppercase;
            margin-right: 5px;
        }
        
        .post-title {
            font-size: 1.8rem;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        
        .post-excerpt {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 20px;
            font-family: 'Courier New', monospace;
            /* Code feel */
        }
        
        .read-btn {
            margin-top: auto;
            align-self: flex-start;
            color: var(--primary-blue);
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: none;
            font-family: 'VT323';
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .read-btn::after {
            content: '->';
            transition: 0.3s;
        }
        
        .post-card:hover .read-btn::after {
            transform: translateX(5px);
            color: var(--accent-color);
        }
        
        .post-card:hover .read-btn {
            color: var(--accent-color);
        }
        
        /* --- FEATURED POST (Wide) --- */
        .post-card.featured {
            grid-column: 1 / -1;
            border-color: var(--primary-blue);
        }
        
        .post-card.featured:hover {
            box-shadow: 8px 8px 0px var(--primary-blue);
        }
        
        /* --- RESPONSIVE --- */
        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                margin-bottom: 40px;
            }
            
            .category-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .category-btn {
                width: auto;
            }
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        header {
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 50px;
        }
        
        nav {
            display: flex;
            justify-content: space-between;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }
        
        .posts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding-bottom: 100px;
        }
        
        .post-card {
            border: 2px solid var(--text-white);
            background: rgba(255, 255, 255, 0.02);
            transition: 0.3s;
            display: flex;
            flex-direction: column;
        }
        
        .post-card:hover {
            transform: translate(-5px, -5px);
            box-shadow: 8px 8px 0px var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .post-header {
            background: var(--primary-blue);
            padding: 10px;
            font-family: 'VT323';
            display: flex;
            justify-content: space-between;
        }
        
        .post-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 2px solid var(--text-white);
        }
        
        .post-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .post-tags span {
            background: var(--accent-color);
            color: black;
            padding: 2px 8px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .post-title {
            font-size: 1.5rem;
            margin: 15px 0;
            line-height: 1.2;
        }
        
        .read-btn {
            margin-top: auto;
            color: var(--primary-blue);
            font-family: 'VT323';
            font-size: 1.2rem;
            text-decoration: none;
            font-weight: bold;
        }
        
        /* تنسيق محتوى المقال عند الفتح (يمكنك عمل صفحة منفصلة single.php لاحقاً) */
        .full-content {
            white-space: pre-wrap;
            font-family: monospace;
            display: none;
        }
    </style>
</head>

<body>
    
    <header>
        <div class="container">
            <nav>
                <div style="font-size:1.5rem; font-weight:bold;">DEV<span style="color:var(--primary-blue)">.CORE</span></div>
                <div>
                    <a href="index.html" style="margin-right:20px">HOME</a>
                    <a href="admin.php" style="color:var(--accent-color)">[ ADMIN_LOGIN ]</a>
                </div>
            </nav>
        </div>
    </header>
    
    <div class="container">
        <h1 style="margin-bottom: 50px; font-size: 3rem;">SYSTEM <span style="color:var(--accent-color)">LOGS</span></h1>
        
        <div class="posts-container">
            <?php if (empty($posts)): ?>
            <p class="funky-text" style="font-size: 1.5rem;">> SYSTEM EMPTY. NO LOGS FOUND.</p>
            <?php else: ?>
            <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div class="post-header">
                    <span>ID: <?php echo substr($post['id'], 0, 8); ?></span>
                    <span><?php echo $post['date']; ?></span>
                </div>
                
                <?php if (!empty($post['image'])): ?>
                <img src="<?php echo $post['image']; ?>" alt="Post Image" class="post-img">
                <?php endif; ?>
                
                <div class="post-content">
                    <div class="post-tags">
                        <?php 
                                    $tags = explode(',', $post['tags']);
                                    foreach($tags as $tag) echo "<span>".trim($tag)."</span>";
                                ?>
                    </div>
                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    
                    <!-- عرض جزء بسيط من النص -->
                    <p style="opacity: 0.8; margin-bottom: 20px; font-family:'VT323'; font-size: 1.1rem;">
                        <?php echo substr(strip_tags($post['content']), 0, 100) . '...'; ?>
                    </p>
                    
                    <a href="single.php?id=<?php echo $post['id']; ?>" class="read-btn">> EXPAND_LOG</a>
                </div>
            </article>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
</body>

</html>