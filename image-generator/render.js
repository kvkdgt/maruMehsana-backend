const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

(async () => {
    try {
        const args = process.argv.slice(2);
        if (args.length < 3) {
            console.error('Usage: node render.js <imagePath> <title> <outputPath>');
            process.exit(1);
        }

        const [imagePath, title, outputPath] = args;

        // Read image as base64
        const imageBuffer = fs.readFileSync(imagePath);
        const imageBase64 = imageBuffer.toString('base64');
        const mimeType = path.extname(imagePath) === '.png' ? 'image/png' : 'image/jpeg';
        const dataUri = `data:${mimeType};base64,${imageBase64}`;

        const tagline = "Your City in Your Pocket";

        const html = `
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { margin: 0; padding: 0; box-sizing: border-box; font-family: sans-serif; }
                .container {
                    position: relative;
                    display: inline-block;
                }
                .main-image {
                    display: block;
                    max-width: 100%;
                    height: auto;
                }
                .footer {
                    position: relative; /* Changed from absolute to flow naturally after image if needed, but designer requested overlay or footer? Previous implementation appended footer. Let's append footer. */
                    width: 100%;
                    background-color: #1c4c8a;
                    color: white;
                    padding: 2.5% 4%; 
                    box-sizing: border-box;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .left-section {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }
                .pin-icon {
                    width: 50px;
                    height: 50px;
                    background: white;
                    border-radius: 50% 50% 50% 0;
                    transform: rotate(-45deg);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin-top: -10px; /* Visual adjustment */
                }
                .pin-content {
                    transform: rotate(45deg);
                    color: #1c4c8a;
                    font-weight: 900;
                    font-size: 24px;
                    margin-bottom: 5px;
                }
                .text-content {
                    display: flex;
                    flex-direction: column;
                }
                .title {
                    font-size: 24px;
                    font-weight: 800;
                    line-height: 1.1;
                    letter-spacing: 0.5px;
                }
                .tagline {
                    font-size: 14px;
                    font-weight: 400;
                    opacity: 0.9;
                }
                .divider {
                    width: 2px;
                    height: 40px;
                    background-color: rgba(255,255,255,0.5);
                    margin: 0 10px;
                }
                .badge {
                    display: flex;
                    align-items: center;
                    border: 2px solid white;
                    border-radius: 10px;
                    padding: 5px 12px;
                    gap: 8px;
                }
                .play-triangle {
                    width: 0; 
                    height: 0; 
                    border-top: 8px solid transparent;
                    border-bottom: 8px solid transparent;
                    border-left: 14px solid white;
                }
                .badge-text-col {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                }
                .badge-label { font-size: 8px; font-weight: 400; line-height: 1; margin-bottom: 2px; }
                .badge-store { font-size: 16px; font-weight: 700; line-height: 1; }
            </style>
        </head>
        <body>
            <div id="capture" style="width: fit-content;">
                <img src="${dataUri}" class="main-image" />
                <div class="footer">
                    <div class="left-section">
                        <div class="pin-icon">
                            <span class="pin-content">M</span>
                        </div>
                        <div class="text-content">
                            <span class="title">MARU MEHSANA</span>
                            <span class="tagline">${tagline}</span>
                        </div>
                    </div>
                    
                    <div class="divider"></div>

                    <div class="badge">
                        <div class="play-triangle"></div>
                        <div class="badge-text-col">
                            <span class="badge-label">Download on</span>
                            <span class="badge-store">Play Store</span>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        `;

        const browser = await puppeteer.launch({
            args: ['--no-sandbox', '--disable-setuid-sandbox'],
            headless: "new"
        });
        const page = await browser.newPage();
        await page.setContent(html);

        const element = await page.$('#capture');
        await element.screenshot({ path: outputPath, type: 'jpeg', quality: 100 });

        await browser.close();
        console.log("Image created successfully: " + outputPath);

    } catch (error) {
        console.error("Error creating image:", error);
        process.exit(1);
    }
})();
