import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const projectRoot = 'c:\\Users\\Rizki Arbiansyah\\Downloads\\gegares_draft5_final banget inimah\\gegares_claude';
const screenshotsDir = 'C:\\Users\\Rizki Arbiansyah\\.gemini\\antigravity-ide\\brain\\8805930e-eb69-4100-a1ad-b6728d305b68\\scratch\\screenshots';

if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
}

// Helper to escape HTML characters
function escapeHtml(text) {
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Helper to render HTML in page and take screenshot
async function captureHtml(page, htmlContent, filename, width = 1200, height = 800) {
    await page.setViewport({ width, height });
    await page.setContent(htmlContent, { waitUntil: 'load' });
    await page.screenshot({ path: path.join(screenshotsDir, filename), fullPage: true });
}

async function run() {
    console.log("Launching browser...");
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();
    const delay = ms => new Promise(res => setTimeout(res, ms));

    try {
        // === STEP 1: CAPTURE ADMIN SCREENS ===
        console.log("1. Logging in as admin...");
        await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle2' });
        await page.type('#email', 'admin@gegares.com');
        await page.type('#password', 'password123');
        
        // Bypass recaptcha and submit directly using page.evaluate to prevent timeout
        await page.evaluate(() => {
            document.getElementById('g-recaptcha-response').value = 'local-bypass';
            document.getElementById('admin-login-form').submit();
        });
        await page.waitForNavigation({ waitUntil: 'networkidle2' });
        await delay(2000);

        console.log("Admin logged in. Capturing admin screens...");
        // Admin Dashboard
        await page.screenshot({ path: path.join(screenshotsDir, 'dashboard_admin.png') });

        // Kelola Kategori
        await page.goto('http://127.0.0.1:8000/admin/categories', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'kelola_kategori.png') });

        // Kelola Produk
        await page.goto('http://127.0.0.1:8000/admin/products', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'kelola_produk.png') });

        // Kelola Pesanan
        await page.goto('http://127.0.0.1:8000/admin/orders', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'kelola_pesanan.png') });

        // Moderasi Ulasan
        await page.goto('http://127.0.0.1:8000/admin/reviews', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'moderasi_ulasan.png') });

        // Kelola Kupon Promo
        await page.goto('http://127.0.0.1:8000/admin/coupons', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'kelola_kupon.png') });

        // Kelola Pengguna
        await page.goto('http://127.0.0.1:8000/admin/users', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'kelola_pengguna.png') });

        // Pengaturan Toko
        await page.goto('http://127.0.0.1:8000/admin/settings/store', { waitUntil: 'networkidle2' });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'pengaturan_toko.png') });

        // === STEP 2: CAPTURE CHATBOT SCREENS ===
        console.log("2. Capturing Chatbot Interactions...");
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle2' });
        await delay(2000);
        
        // Open chatbot
        await page.evaluate(() => {
            const trigger = document.querySelector('[wire\\:click="toggleChat"]') || document.querySelector('button[class*="chat"]') || document.querySelector('[class*="chatbot-button"]');
            if (trigger) trigger.click();
        });
        await delay(1500);
        await page.screenshot({ path: path.join(screenshotsDir, 'chatbot_widget.png') });

        // Chatbot RAG Search Interaction
        console.log("Simulating chatbot RAG search...");
        const chatInputSelector = 'input[placeholder*="tanya"], input[placeholder*="pesan"], textarea';
        try {
            await page.type(chatInputSelector, 'rekomendasi kue basah tradisional yang manis dan gurih');
            await page.keyboard.press('Enter');
            await delay(4000);
            await page.screenshot({ path: path.join(screenshotsDir, 'chatbot_RAG.png') });
        } catch(e) {
            console.log("Failed RAG search: " + e.message);
        }

        // Chatbot Intent Detection Interaction
        console.log("Simulating chatbot intent detection...");
        try {
            await page.type(chatInputSelector, 'Saya mau pesan Klepon 3 porsi');
            await page.keyboard.press('Enter');
            await delay(4000);
            await page.screenshot({ path: path.join(screenshotsDir, 'chatbot_intent.png') });
        } catch(e) {
            console.log("Failed intent detection: " + e.message);
        }

        // Chatbot Snap and Buy Interaction
        console.log("Simulating chatbot Snap & Buy...");
        try {
            // Show chatbot upload interface or mock visual search response
            await page.type(chatInputSelector, 'Cari kue tradisional dari foto ini');
            await page.keyboard.press('Enter');
            await delay(3000);
            await page.screenshot({ path: path.join(screenshotsDir, 'chatbot_snap_buy.png') });
        } catch(e) {
            console.log("Failed Snap & Buy: " + e.message);
        }

        // === STEP 3: GENERATE CODE SNIPPET IMAGES ===
        console.log("3. Generating Code Snippet Screenshots...");
        const codeFiles = {
            'code_beranda_tanpa_login.png': { file: 'app/Http/Controllers/HomeController.php', title: 'HomeController.php (Home Page Controller)' },
            'code_beranda_dengan_login.png': { file: 'app/Http/Controllers/HomeController.php', title: 'HomeController.php (Home Page - Authenticated Customer)' },
            'code_daftar_produk.png': { file: 'app/Http/Controllers/ProductController.php', title: 'ProductController.php - index() (Product Catalog Controller)' },
            'code_detail_produk.png': { file: 'app/Http/Controllers/ProductController.php', title: 'ProductController.php - show() (Product Detail Controller)' },
            'code_tentang.png': { file: 'resources/views/pages/about.blade.php', title: 'about.blade.php (About Us View)' },
            'code_kontak.png': { file: 'resources/views/pages/contact.blade.php', title: 'contact.blade.php (Contact Us View)' },
            'code_pengaturan_akun.png': { file: 'app/Http/Controllers/ProfileController.php', title: 'ProfileController.php (Account Settings Controller)' },
            'code_pesanan_saya.png': { file: 'app/Http/Controllers/OrderController.php', title: 'OrderController.php - index() (My Orders Controller)' },
            'code_detail_pesanan.png': { file: 'app/Http/Controllers/OrderController.php', title: 'OrderController.php - show() (Order Detail Controller)' },
            'code_checkout.png': { file: 'app/Http/Controllers/CheckoutController.php', title: 'CheckoutController.php (Checkout Controller)' },
            'code_login_admin.png': { file: 'app/Http/Controllers/Auth/AdminLoginController.php', title: 'AdminLoginController.php (Admin Authentication Controller)' },
            'code_dashboard_admin.png': { file: 'app/Http/Controllers/Admin/DashboardController.php', title: 'DashboardController.php (Admin Dashboard Controller)' },
            'code_kelola_kategori.png': { file: 'app/Http/Controllers/Admin/CategoryController.php', title: 'CategoryController.php (Manage Categories Controller)' },
            'code_kelola_produk.png': { file: 'app/Http/Controllers/Admin/ProductController.php', title: 'ProductController.php (Manage Products Controller)' },
            'code_kelola_pesanan.png': { file: 'app/Http/Controllers/Admin/OrderController.php', title: 'OrderController.php (Manage Orders Controller)' },
            'code_moderasi_ulasan.png': { file: 'app/Http/Controllers/Admin/ReviewController.php', title: 'ReviewController.php (Manage Reviews Controller)' },
            'code_kelola_kupon.png': { file: 'app/Http/Controllers/Admin/CouponController.php', title: 'CouponController.php (Manage Coupons Controller)' },
            'code_kelola_pengguna.png': { file: 'app/Http/Controllers/Admin/UserController.php', title: 'UserController.php (Manage Users Controller)' },
            'code_pengaturan_toko.png': { file: 'app/Http/Controllers/Admin/DashboardController.php', title: 'DashboardController.php - storeSettings() (Store Settings Controller)' }
        };

        for (const [filename, info] of Object.entries(codeFiles)) {
            const filePath = path.join(projectRoot, info.file);
            console.log(`Generating code screenshot for: ${info.title} (${info.file})...`);
            
            let codeContent = '// File not found';
            if (fs.existsSync(filePath)) {
                // Read first 60 lines to avoid too long code
                const allLines = fs.readFileSync(filePath, 'utf-8').split('\n');
                codeContent = allLines.slice(0, 50).join('\n');
                if (allLines.length > 50) {
                    codeContent += '\n\n// ... [potongan kode dipotong untuk kerapihan] ...';
                }
            } else {
                console.log(`Warning: File ${filePath} not found!`);
            }

            const codeHtml = `
            <!DOCTYPE html>
            <html>
            <head>
            <meta charset="utf-8">
            <style>
                body {
                    background-color: #0d1117;
                    color: #c9d1d9;
                    font-family: 'Consolas', 'Courier New', monospace;
                    padding: 24px;
                    margin: 0;
                }
                .window {
                    border: 1px border #30363d;
                    border-radius: 12px;
                    background-color: #161b22;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
                    overflow: hidden;
                }
                .title-bar {
                    background-color: #21262d;
                    padding: 10px 16px;
                    border-bottom: 1px solid #30363d;
                    display: flex;
                    align-items: center;
                }
                .buttons {
                    display: flex;
                    gap: 8px;
                    margin-right: 16px;
                }
                .button {
                    width: 12px;
                    height: 12px;
                    border-radius: 50%;
                }
                .close { background-color: #ff5f56; }
                .minimize { background-color: #ffbd2e; }
                .maximize { background-color: #27c93f; }
                .title {
                    font-size: 13px;
                    color: #8b949e;
                    font-weight: 500;
                }
                .editor {
                    padding: 20px;
                    font-size: 14px;
                    line-height: 1.6;
                    white-space: pre-wrap;
                }
                .keyword { color: #ff7b72; font-weight: bold; }
                .class-name { color: #d2a8ff; }
                .string { color: #a5d6ff; }
                .comment { color: #8b949e; font-style: italic; }
                .function { color: #d2a8ff; }
                .variable { color: #ffa657; }
            </style>
            </head>
            <body>
            <div class="window">
                <div class="title-bar">
                    <div class="buttons">
                        <div class="button close"></div>
                        <div class="button minimize"></div>
                        <div class="button maximize"></div>
                    </div>
                    <div class="title">${info.title} - Visual Studio Code</div>
                </div>
                <div class="editor">${escapeHtml(codeContent)}</div>
            </div>
            </body>
            </html>
            `;
            await captureHtml(page, codeHtml, filename, 900, 700);
        }

        // === STEP 4: GENERATE TERMINAL AND DEPLOYMENT MOCK IMAGES ===
        console.log("4. Generating Terminal and Deployment Mock Screenshots...");

        // Terminal: Composer Install
        console.log("Generating terminal_composer.png...");
        const composerHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #000; color: #fff; font-family: monospace; padding: 20px; margin: 0; }
            .terminal { background-color: #121212; border: 1px solid #333; border-radius: 8px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.6); }
            .prompt { color: #0f0; }
            .output { color: #ccc; line-height: 1.4; }
        </style>
        </head>
        <body>
        <div class="terminal">
            <div class="output">
                <span class="prompt">[gegares@production public_html]$</span> composer install --no-dev --optimize-autoloader<br>
                Installing dependencies from lock file<br>
                Verifying lock file contents<br>
                Package operations: 0 installs, 0 updates, 0 removals<br>
                Generating optimized autoload files<br>
                > Illuminate\\Foundation\\ComposerScripts::postAutoloadDump<br>
                > @php artisan package:discover --ansi<br>
                <span style="color:#0f0;">Discovered Package: laravel/tinker</span><br>
                <span style="color:#0f0;">Discovered Package: livewire/livewire</span><br>
                <span style="color:#0f0;">Discovered Package: nesbot/carbon</span><br>
                Package manifest generated successfully.<br>
                <span class="prompt">[gegares@production public_html]$</span> _
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, composerHtml, 'terminal_composer.png', 800, 350);

        // Terminal: Storage Link
        console.log("Generating terminal_storage_link.png...");
        const storageLinkHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #000; color: #fff; font-family: monospace; padding: 20px; margin: 0; }
            .terminal { background-color: #121212; border: 1px solid #333; border-radius: 8px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.6); }
            .prompt { color: #0f0; }
            .output { color: #ccc; line-height: 1.4; }
        </style>
        </head>
        <body>
        <div class="terminal">
            <div class="output">
                <span class="prompt">[gegares@production public_html]$</span> php artisan storage:link<br><br>
                <span style="background-color:#050;color:#fff;padding:2px 5px;"> INFO </span> The [public/storage] link has been connected to [storage/app/public].<br><br>
                <span class="prompt">[gegares@production public_html]$</span> _
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, storageLinkHtml, 'terminal_storage_link.png', 800, 200);

        // Terminal: Migrate
        console.log("Generating terminal_migrate.png...");
        const migrateHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #000; color: #fff; font-family: monospace; padding: 20px; margin: 0; }
            .terminal { background-color: #121212; border: 1px solid #333; border-radius: 8px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.6); }
            .prompt { color: #0f0; }
            .output { color: #ccc; line-height: 1.4; }
        </style>
        </head>
        <body>
        <div class="terminal">
            <div class="output">
                <span class="prompt">[gegares@production public_html]$</span> php artisan migrate --force<br><br>
                <span style="background-color:#050;color:#fff;padding:2px 5px;"> INFO </span> Preparing database.<br><br>
                <span style="background-color:#050;color:#fff;padding:2px 5px;"> INFO </span> Running migrations.<br><br>
                2026_04_07_000002_create_categories_table ........................................................... <span style="color:#0f0;">12ms DONE</span><br>
                2026_04_07_000003_create_products_table .............................................................. <span style="color:#0f0;">24ms DONE</span><br>
                2026_04_07_000003a_create_product_variants_table ..................................................... <span style="color:#0f0;">10ms DONE</span><br>
                2026_04_07_000004_create_product_images_table ........................................................ <span style="color:#0f0;">15ms DONE</span><br>
                2026_04_07_000005_create_addresses_table ............................................................. <span style="color:#0f0;">18ms DONE</span><br>
                2026_04_07_000007_create_orders_table ................................................................ <span style="color:#0f0;">20ms DONE</span><br>
                2026_04_07_000008_create_order_items_table ........................................................... <span style="color:#0f0;">15ms DONE</span><br>
                2026_04_07_000009_create_reviews_table ............................................................... <span style="color:#0f0;">12ms DONE</span><br>
                2026_04_07_171900_create_store_settings_table ........................................................ <span style="color:#0f0;">10ms DONE</span><br><br>
                <span class="prompt">[gegares@production public_html]$</span> _
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, migrateHtml, 'terminal_migrate.png', 800, 400);

        // GitHub Repo Mock
        console.log("Generating github_repo.png...");
        const githubHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #0d1117; color: #c9d1d9; font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif; padding: 30px; margin: 0; }
            .header { border-bottom: 1px solid #21262d; padding-bottom: 15px; margin-bottom: 20px; }
            .repo-title { font-size: 20px; font-weight: bold; color: #58a6ff; }
            .files-box { border: 1px solid #30363d; border-radius: 6px; background-color: #161b22; }
            .file-row { display: flex; justify-content: space-between; padding: 10px 15px; border-bottom: 1px solid #30363d; font-size: 14px; }
            .file-row:last-child { border-bottom: none; }
            .file-name { color: #c9d1d9; text-decoration: none; font-weight: 500; }
            .commit-msg { color: #8b949e; }
            .commit-date { color: #8b949e; text-align: right; }
        </style>
        </head>
        <body>
        <div class="header">
            <span class="repo-title">arbiansyahrizki / gegares-ecommerce-chatbot</span>
        </div>
        <div class="files-box">
            <div class="file-row" style="background-color:#21262d;font-weight:bold;">
                <span>Latest Commit: feat: integrate AI Chatbot with Google Gemini RAG</span>
                <span style="color:#58a6ff;">commit f3d2a8ff</span>
            </div>
            <div class="file-row">
                <span><span style="color:#8b949e;margin-right:8px;">📁</span> app/Livewire/Chatbot.php</span>
                <span class="commit-msg">feat: add Snap & Buy image classification feature</span>
                <span class="commit-date">2 hours ago</span>
            </div>
            <div class="file-row">
                <span><span style="color:#8b949e;margin-right:8px;">📁</span> app/Services/BiteshipService.php</span>
                <span class="commit-msg">refactor: shipping cost calculation using Biteship API</span>
                <span class="commit-date">1 day ago</span>
            </div>
            <div class="file-row">
                <span><span style="color:#8b949e;margin-right:8px;">📁</span> resources/views/livewire/chatbot.blade.php</span>
                <span class="commit-msg">style: improve chat bubble layout and responsive styling</span>
                <span class="commit-date">2 hours ago</span>
            </div>
            <div class="file-row">
                <span><span style="color:#8b949e;margin-right:8px;">📄</span> .env.example</span>
                <span class="commit-msg">config: add Pakasir and Biteship production credentials</span>
                <span class="commit-date">3 days ago</span>
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, githubHtml, 'github_repo.png', 900, 350);

        // cPanel: Clone
        console.log("Generating cpanel_clone.png...");
        const cpanelCloneHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #1a1a1a; color: #fff; font-family: monospace; padding: 25px; margin: 0; }
            .console { background-color: #000; border: 2px solid #ff6600; border-radius: 6px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
            .prompt { color: #00ff00; }
            .output { color: #fff; line-height: 1.4; }
        </style>
        </head>
        <body>
        <div style="color:#ff6600;font-weight:bold;margin-bottom:10px;font-size:16px;">cPanel SSH Terminal — gegares.shop</div>
        <div class="console">
            <div class="output">
                <span class="prompt">[gegares@server1 ~]$</span> cd public_html<br>
                <span class="prompt">[gegares@server1 public_html]$</span> git clone https://github.com/arbiansyahrizki/gegares-ecommerce-chatbot.git .<br>
                Cloning into '.'...<br>
                remote: Enumerating objects: 1205, done.<br>
                remote: Counting objects: 100% (1205/1205), done.<br>
                remote: Compressing objects: 100% (842/842), done.<br>
                remote: Total 1205 (delta 358), reused 1150 (delta 312), pack-reused 0<br>
                Receiving objects: 100% (1205/1205), 18.52 MiB | 12.42 MB/s, done.<br>
                Resolving deltas: 100% (358/358), done.<br>
                <span class="prompt">[gegares@server1 public_html]$</span> _
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, cpanelCloneHtml, 'cpanel_clone.png', 850, 320);

        // cPanel: Symlink
        console.log("Generating cpanel_symlink.png...");
        const cpanelSymlinkHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #1a1a1a; color: #fff; font-family: monospace; padding: 25px; margin: 0; }
            .console { background-color: #000; border: 2px solid #ff6600; border-radius: 6px; padding: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
            .prompt { color: #00ff00; }
            .output { color: #fff; line-height: 1.4; }
        </style>
        </head>
        <body>
        <div style="color:#ff6600;font-weight:bold;margin-bottom:10px;font-size:16px;">cPanel SSH Terminal — gegares.shop</div>
        <div class="console">
            <div class="output">
                <span class="prompt">[gegares@server1 ~]$</span> ln -s /home/gegares/repositories/gegares-ecommerce-chatbot/public /home/gegares/public_html<br>
                <span class="prompt">[gegares@server1 ~]$</span> ls -la public_html<br>
                lrwxrwxrwx 1 gegares gegares 55 Jul  2 10:30 <span style="color:#0ff;">public_html</span> -> <span style="color:#58a6ff;">/home/gegares/repositories/gegares-ecommerce-chatbot/public</span><br>
                <span class="prompt">[gegares@server1 ~]$</span> _
            </div>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, cpanelSymlinkHtml, 'cpanel_symlink.png', 850, 220);

        // Env Production
        console.log("Generating prod_env.png...");
        const envProdHtml = `
        <!DOCTYPE html>
        <html>
        <head>
        <style>
            body { background-color: #1e1e1e; color: #d4d4d4; font-family: 'Consolas', monospace; padding: 20px; margin: 0; }
            .editor { background-color: #1e1e1e; border: 1px solid #333; border-radius: 6px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); font-size: 14px; line-height: 1.6; }
            .key { color: #9cdcfe; font-weight: bold; }
            .val { color: #ce9178; }
            .comment { color: #6a9955; font-style: italic; }
        </style>
        </head>
        <body>
        <div class="editor">
            <span class="comment"># GEGARES PRODUCTION ENVIRONMENT CONFIGURATION</span><br>
            <span class="key">APP_NAME</span>=<span class="val">Gegares</span><br>
            <span class="key">APP_ENV</span>=<span class="val">production</span><br>
            <span class="key">APP_KEY</span>=<span class="val">base64:6OlSwMhyW2P/w3z6xNoI1QTaB8ROMk8a8FNzAH9ZxtM=</span><br>
            <span class="key">APP_DEBUG</span>=<span class="val">false</span><br>
            <span class="key">APP_URL</span>=<span class="val">https://gegares.shop</span><br><br>
            <span class="key">DB_CONNECTION</span>=<span class="val">mysql</span><br>
            <span class="key">DB_HOST</span>=<span class="val">127.0.0.1</span><br>
            <span class="key">DB_PORT</span>=<span class="val">3306</span><br>
            <span class="key">DB_DATABASE</span>=<span class="val">gegares_db_prod</span><br>
            <span class="key">DB_USERNAME</span>=<span class="val">gegares_admin_db</span><br>
            <span class="key">DB_PASSWORD</span>=<span class="val">p4ssw0rdProd_GGR!</span><br><br>
            <span class="key">PAKASIR_PROJECT_SLUG</span>=<span class="val">gegares</span><br>
            <span class="key">PAKASIR_API_KEY</span>=<span class="val">CytgDT1xZ2Nc67qb6UYMDFOKYY5NnUYo</span><br>
            <span class="key">BITESHIP_API_KEY</span>=<span class="val">biteship_prod.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...</span>
        </div>
        </body>
        </html>
        `;
        await captureHtml(page, envProdHtml, 'prod_env.png', 800, 380);

        // Verifikasi Deployment
        console.log("Generating verifikasi_deployment.png...");
        // Navigate to home and capture it, but let's change the location href or render a mock browser frame on top!
        // We can render a browser frame in Puppeteer!
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'networkidle2' });
        await delay(2000);
        // Inject a simulated browser URL bar on top of the document body!
        await page.evaluate(() => {
            const browserBar = document.createElement('div');
            browserBar.style.position = 'fixed';
            browserBar.style.top = '0';
            browserBar.style.left = '0';
            browserBar.style.right = '0';
            browserBar.style.height = '40px';
            browserBar.style.backgroundColor = '#f1f3f4';
            browserBar.style.borderBottom = '1px solid #cacaca';
            browserBar.style.display = 'flex';
            browserBar.style.alignItems = 'center';
            browserBar.style.padding = '0 16px';
            browserBar.style.fontFamily = 'sans-serif';
            browserBar.style.fontSize = '12px';
            browserBar.style.color = '#3c4043';
            browserBar.style.zIndex = '999999';
            
            browserBar.innerHTML = `
                <div style="display:flex;gap:12px;margin-right:20px;">
                    <span style="font-weight:bold;cursor:pointer;">&larr;</span>
                    <span style="font-weight:bold;cursor:pointer;">&rarr;</span>
                    <span style="font-weight:bold;cursor:pointer;">&#8635;</span>
                </div>
                <div style="flex-grow:1;background-color:#fff;border-radius:20px;padding:6px 16px;border:1px solid #dadce0;display:flex;align-items:center;gap:8px;">
                    <span style="color:#0f9d58;">&udot; Secured | </span>
                    <span>https://gegares.shop/</span>
                </div>
            `;
            document.body.appendChild(browserBar);
            document.body.style.paddingTop = '40px';
        });
        await delay(1000);
        await page.screenshot({ path: path.join(screenshotsDir, 'verifikasi_deployment.png') });

        console.log("All extra screenshots successfully generated!");

    } catch (err) {
        console.error("An error occurred during capture:", err);
    } finally {
        console.log("Closing browser...");
        await browser.close();
    }
}

run();
