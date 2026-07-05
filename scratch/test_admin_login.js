import puppeteer from 'puppeteer';

async function run() {
    console.log("Launching browser...");
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    const page = await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    page.on('console', msg => console.log('PAGE LOG:', msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.toString()));

    try {
        console.log("Navigating to admin login...");
        await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'load' });
        await page.waitForSelector('#admin-login-form');

        console.log("Filling form...");
        await page.type('#email', 'admin@gegares.com');
        await page.type('#password', 'password123');

        console.log("Submitting form...");
        // Let's set recaptcha-response to local-bypass and submit
        await page.evaluate(() => {
            document.getElementById('g-recaptcha-response').value = 'local-bypass';
        });

        await Promise.all([
            page.click('button[type="submit"]'),
            page.waitForNavigation({ waitUntil: 'load', timeout: 15000 })
        ]);

        console.log("Current URL after login submit:", page.url());
        
        // Take a screenshot of what we got
        await page.screenshot({ path: 'scratch_admin_login_result.png' });
        
        // Let's print any errors shown on the page
        const errorText = await page.evaluate(() => {
            const el = document.querySelector('.bg-red-500\\/10');
            return el ? el.innerText : 'No errors found on page';
        });
        console.log("Errors on page:", errorText);

    } catch (err) {
        console.error("Error during test:", err);
    } finally {
        await browser.close();
    }
}

run();
