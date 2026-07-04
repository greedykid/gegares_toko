import puppeteer from 'puppeteer';
import fs from 'fs';
import path from 'path';

const screenshotsDir = 'C:\\Users\\Rizki Arbiansyah\\.gemini\\antigravity-ide\\brain\\8805930e-eb69-4100-a1ad-b6728d305b68\\scratch\\screenshots';

if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
}

async function run() {
    console.log("Launching client browser...");
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.toString()));

    try {
        const delay = ms => new Promise(res => setTimeout(res, ms));

        // 1. Beranda Tanpa Login
        console.log("1. Capturing Home (Guest)...");
        await page.goto('http://127.0.0.1:8000', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2000);
        await page.screenshot({ path: path.join(screenshotsDir, 'beranda_tanpa_login.png') });

        // 2. Login User Page
        console.log("2. Capturing Login Page...");
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(1000);
        await page.screenshot({ path: path.join(screenshotsDir, 'login_user.png') });

        // 3. Register User Page
        console.log("3. Capturing Register Page...");
        await page.goto('http://127.0.0.1:8000/register', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(1000);
        await page.screenshot({ path: path.join(screenshotsDir, 'register_user.png') });

        // 4. Perform User Login
        console.log("Logging in as user client@example.com...");
        await page.goto('http://127.0.0.1:8000/login', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await page.type('input[type="email"]', 'client@example.com');
        await page.type('input[type="password"]', 'password123');
        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'load', timeout: 10000 }).catch(() => {})
        ]);
        await delay(2500);

        // 5. Beranda Dengan Login
        console.log("5. Capturing Home (Logged In)...");
        await page.screenshot({ path: path.join(screenshotsDir, 'beranda_dengan_login.png') });

        // 6. Daftar Produk
        console.log("6. Capturing Product List...");
        await page.goto('http://127.0.0.1:8000/products', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2000);
        await page.screenshot({ path: path.join(screenshotsDir, 'daftar_produk.png') });

        // 7. Detail Produk (Klepon)
        console.log("7. Capturing Product Detail...");
        await page.goto('http://127.0.0.1:8000/products/klepon', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2000);
        await page.screenshot({ path: path.join(screenshotsDir, 'detail_produk.png') });

        // Add to Cart
        console.log("Adding product to cart...");
        try {
            await page.evaluate(() => {
                const buttons = Array.from(document.querySelectorAll('button'));
                const buyBtn = buttons.find(b => b.textContent.includes('Keranjang') || b.textContent.includes('Beli') || b.textContent.includes('Tambah'));
                if (buyBtn) buyBtn.click();
            });
            await delay(2000);
        } catch (e) {
            console.log("Failed to add to cart: " + e.message);
        }

        // 8. Tentang
        console.log("8. Capturing About Page...");
        await page.goto('http://127.0.0.1:8000/about', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(1000);
        await page.screenshot({ path: path.join(screenshotsDir, 'tentang.png') });

        // 9. Kontak
        console.log("9. Capturing Contact Page...");
        await page.goto('http://127.0.0.1:8000/contact', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(1000);
        await page.screenshot({ path: path.join(screenshotsDir, 'kontak.png') });

        // 10. Pengaturan Akun
        console.log("10. Capturing Settings Page...");
        await page.goto('http://127.0.0.1:8000/settings', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2000);
        await page.screenshot({ path: path.join(screenshotsDir, 'pengaturan_akun.png') });

        // 11. Pesanan Saya
        console.log("11. Capturing My Orders...");
        await page.goto('http://127.0.0.1:8000/orders', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2000);
        await page.screenshot({ path: path.join(screenshotsDir, 'pesanan_saya.png') });

        // 12. Detail Pesanan (ID 1)
        console.log("12. Capturing Order Detail...");
        await page.goto('http://127.0.0.1:8000/orders/1', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await page.screenshot({ path: path.join(screenshotsDir, 'detail_pesanan.png') });

        // 13. Checkout
        console.log("13. Capturing Checkout...");
        await page.goto('http://127.0.0.1:8000/checkout', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await page.screenshot({ path: path.join(screenshotsDir, 'checkout.png') });

    } catch (err) {
        console.error("An error occurred during client Puppeteer capture:", err);
    } finally {
        console.log("Closing client browser...");
        await browser.close();
    }

    // ==========================================
    // ADMIN BROWSER FLOW (FRESH INCIDENT-FREE SESSION)
    // ==========================================
    console.log("Launching fresh admin browser...");
    const adminBrowser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const adminPage = await adminBrowser.newPage();
    await adminPage.setViewport({ width: 1366, height: 768 });

    adminPage.on('console', msg => console.log('ADMIN PAGE LOG:', msg.text()));
    adminPage.on('pageerror', err => console.log('ADMIN PAGE ERROR:', err.toString()));

    try {
        const delay = ms => new Promise(res => setTimeout(res, ms));

        // 14. Admin Login Page
        console.log("14. Capturing Admin Login...");
        await adminPage.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(1500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'login_admin.png') });

        // 15. Perform Admin Login
        console.log("Logging in as admin...");
        await adminPage.type('#email', 'admin@gegares.com');
        await adminPage.type('#password', 'password123');
        await adminPage.evaluate(() => {
            document.getElementById('g-recaptcha-response').value = 'local-bypass';
        });
        await Promise.all([
            adminPage.click('button[type="submit"]'),
            adminPage.waitForNavigation({ waitUntil: 'load', timeout: 15000 }).catch(() => {})
        ]);
        await delay(3000);

        // 16. Admin Dashboard
        console.log("16. Capturing Admin Dashboard...");
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'dashboard_admin.png') });

        // 17. Kelola Kategori
        console.log("17. Capturing Manage Categories...");
        await adminPage.goto('http://127.0.0.1:8000/admin/categories', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'kelola_kategori.png') });

        // 18. Kelola Produk
        console.log("18. Capturing Manage Products...");
        await adminPage.goto('http://127.0.0.1:8000/admin/products', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'kelola_produk.png') });

        // 19. Kelola Pesanan
        console.log("19. Capturing Manage Orders...");
        await adminPage.goto('http://127.0.0.1:8000/admin/orders', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'kelola_pesanan.png') });

        // 20. Moderasi Ulasan
        console.log("20. Capturing Moderation Reviews...");
        await adminPage.goto('http://127.0.0.1:8000/admin/reviews', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'moderasi_ulasan.png') });

        // 21. Kelola Kupon Promo
        console.log("21. Capturing Manage Coupons...");
        await adminPage.goto('http://127.0.0.1:8000/admin/coupons', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'kelola_kupon.png') });

        // 22. Kelola Pengguna
        console.log("22. Capturing Manage Users...");
        await adminPage.goto('http://127.0.0.1:8000/admin/users', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'kelola_pengguna.png') });

        // 23. Pengaturan Toko
        console.log("23. Capturing Store Settings...");
        await adminPage.goto('http://127.0.0.1:8000/admin/settings/store', { waitUntil: 'load', timeout: 15000 }).catch(() => {});
        await delay(2500);
        await adminPage.screenshot({ path: path.join(screenshotsDir, 'pengaturan_toko.png') });

    } catch (err) {
        console.error("An error occurred during admin Puppeteer capture:", err);
    } finally {
        console.log("Closing admin browser...");
        await adminBrowser.close();
    }
}

run();
