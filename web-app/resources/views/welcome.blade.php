<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQS - Object Measurement Tool</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --surface-color: rgba(30, 41, 59, 0.7);
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --accent-color: #10b981;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.15) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 2rem;
        }

        header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInDown 0.8s ease-out;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #60a5fa, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }

        p.subtitle {
            color: var(--text-muted);
            font-weight: 300;
        }

        .main-container {
            display: flex;
            gap: 2rem;
            width: 100%;
            max-width: 1200px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .glass-panel {
            background: var(--surface-color);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease-out backwards;
        }

        .camera-section {
            flex: 1;
            min-width: 300px;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation-delay: 0.1s;
        }

        .video-container {
            width: 100%;
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            background: #000;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
            aspect-ratio: 4/3;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror effect */
        }

        .btn {
            margin-top: 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -3px rgba(59, 130, 246, 0.6);
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn:disabled {
            background: #475569;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .results-section {
            flex: 0.5;
            min-width: 300px;
            animation-delay: 0.2s;
            display: flex;
            flex-direction: column;
        }

        .results-header {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .results-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex-grow: 1;
            overflow-y: auto;
        }

        .result-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
            border-left: 4px solid var(--primary-color);
            transition: transform 0.2s;
        }

        .result-card:hover {
            transform: translateX(5px);
        }

        .result-card.reference {
            border-left-color: var(--accent-color);
        }

        .result-type {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .result-dims {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .result-dims span {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 400;
        }

        .loading {
            display: none;
            width: 24px;
            height: 24px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        /* Instructions */
        .instructions {
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            background: rgba(0,0,0,0.2);
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .instructions ul {
            padding-left: 1.5rem;
            margin-top: 0.5rem;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <header>
        <h1>Smart Measure</h1>
        <p class="subtitle">AI-Powered Physical Object Measurement</p>
    </header>

    <div class="main-container">
        
        <!-- Camera Section -->
        <section class="glass-panel camera-section">
            <div class="video-container" id="videoWrapper">
                <video id="webcam" autoplay playsinline></video>
                <img id="resultImage" style="display:none; width: 100%; height: 100%; object-fit: contain;" />
                <!-- Hidden canvas for capturing frames -->
                <canvas id="canvas" style="display:none;"></canvas>
            </div>
            
            <button id="captureBtn" class="btn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
                <span>Capture & Measure</span>
                <div class="loading" id="loadingSpinner"></div>
            </button>

            <div class="instructions">
                <strong>How to use:</strong>
                <ul>
                    <li>Place a US Quarter coin (2.426cm) on the left side.</li>
                    <li>Place the objects you want to measure on the right.</li>
                    <li>Ensure the camera is looking straight down (bird's eye view).</li>
                    <li>Click Capture!</li>
                </ul>
            </div>
        </section>

        <!-- Results Section -->
        <section class="glass-panel results-section">
            <div class="results-header">
                Measurement Results
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-color)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            
            <ul class="results-list" id="resultsList">
                <li style="color: var(--text-muted); text-align: center; margin-top: 2rem;">
                    Waiting for capture...
                </li>
            </ul>
        </section>

    </div>

    <script>
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('canvas');
        const ctx = canvas.getContext('2d');
        const captureBtn = document.getElementById('captureBtn');
        const btnText = captureBtn.querySelector('span');
        const spinner = document.getElementById('loadingSpinner');
        const resultsList = document.getElementById('resultsList');

        // Python API Endpoint
        const API_URL = 'http://127.0.0.1:8000/measure';

        // 1. Initialize Webcam
        async function startCamera() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } } 
                });
                video.srcObject = stream;
            } catch (err) {
                console.error("Error accessing webcam:", err);
                resultsList.innerHTML = `<li style="color: #ef4444;">Camera access denied or unavailable.</li>`;
            }
        }

        startCamera();

        // 2. Capture and Send
        captureBtn.addEventListener('click', () => {
            // If it says Retake Photo, reset
            if (btnText.innerText === 'Retake Photo') {
                document.getElementById('webcam').style.display = 'block';
                document.getElementById('resultImage').style.display = 'none';
                btnText.innerText = 'Capture & Measure';
                resultsList.innerHTML = '<li style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Waiting for capture...</li>';
                return;
            }

            // UI Loading state
            captureBtn.disabled = true;
            btnText.style.display = 'none';
            spinner.style.display = 'block';
            resultsList.innerHTML = '<li style="color: var(--text-muted); text-align: center; margin-top: 2rem;">Analyzing image...</li>';

            // Set canvas size to video actual size
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            
            // Draw current video frame to canvas
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert to Blob and send
            canvas.toBlob(async (blob) => {
                const formData = new FormData();
                formData.append('image', blob, 'capture.jpg');

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        body: formData
                    });

                    if (!response.ok) throw new Error('API Error');

                    const data = await response.json();
                    renderResults(data);

                } catch (error) {
                    console.error('Error:', error);
                    resultsList.innerHTML = `<li style="color: #ef4444;">Error connecting to Python Vision Engine. Is it running?</li>`;
                } finally {
                    // Reset UI
                    captureBtn.disabled = false;
                    btnText.style.display = 'inline';
                    spinner.style.display = 'none';
                }
            }, 'image/jpeg', 0.95);
        });

        // 3. Render Results
        function renderResults(data) {
            if (data.status !== 'success') {
                resultsList.innerHTML = `<li style="color: #ef4444;">${data.message || 'Failed to detect objects.'}</li>`;
                return;
            }

            resultsList.innerHTML = ''; // Clear

            data.measurements.forEach((item, index) => {
                const li = document.createElement('li');
                li.className = `result-card`;
                
                li.innerHTML = `
                    <div class="result-type">${item.class.toUpperCase()} Detected</div>
                    <div class="result-dims">
                        ${item.width_cm} <span>cm</span> &times; ${item.height_cm} <span>cm</span>
                    </div>
                `;
                resultsList.appendChild(li);
            });
            
            // Show the returned image
            if (data.image_base64) {
                document.getElementById('webcam').style.display = 'none';
                const resultImg = document.getElementById('resultImage');
                resultImg.style.display = 'block';
                resultImg.src = 'data:image/jpeg;base64,' + data.image_base64;
                btnText.innerText = 'Retake Photo';
            }
        }
    </script>
</body>
</html>
