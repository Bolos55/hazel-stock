<?php
/**
 * API Debug Tool
 * Shows raw API responses to help debug JSON parsing issues
 */
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug Tool</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .debug-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 2rem;
        }
        .api-test {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .response-box {
            background: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
            white-space: pre-wrap;
        }
        .status-success { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .btn-test {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
            margin-right: 0.5rem;
        }
        .btn-test:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-red-600 mb-2">🔍 API Debug Tool</h1>
            <p class="text-gray-600">ตรวจสอบ API responses และแก้ไขปัญหา JSON parsing</p>
        </div>

        <div class="api-test">
            <h3 class="font-semibold mb-3">📊 Get Materials API</h3>
            <button class="btn-test" onclick="testAPI('/api/get-materials.php', 'materials')">Test API</button>
            <button class="btn-test" onclick="clearResult('materials')">Clear</button>
            <div id="materials-status" class="mt-2"></div>
            <div id="materials-response" class="response-box" style="display: none;"></div>
        </div>

        <div class="api-test">
            <h3 class="font-semibold mb-3">📅 Get Today Record API</h3>
            <button class="btn-test" onclick="testAPI('/api/get-today-record.php', 'today')">Test API</button>
            <button class="btn-test" onclick="clearResult('today')">Clear</button>
            <div id="today-status" class="mt-2"></div>
            <div id="today-response" class="response-box" style="display: none;"></div>
        </div>

        <div class="api-test">
            <h3 class="font-semibold mb-3">🔧 System Test</h3>
            <button class="btn-test" onclick="testAPI('/test-basic.php', 'system')">Test System</button>
            <button class="btn-test" onclick="clearResult('system')">Clear</button>
            <div id="system-status" class="mt-2"></div>
            <div id="system-response" class="response-box" style="display: none;"></div>
        </div>

        <div class="api-test">
            <h3 class="font-semibold mb-3">⚙️ Config Test</h3>
            <button class="btn-test" onclick="testAPI('/test-config.php', 'config')">Test Config</button>
            <button class="btn-test" onclick="clearResult('config')">Clear</button>
            <div id="config-status" class="mt-2"></div>
            <div id="config-response" class="response-box" style="display: none;"></div>
        </div>

        <div class="text-center mt-6">
            <a href="/" class="btn-primary" style="display: inline-block; text-decoration: none; margin-right: 1rem;">🏠 กลับหน้าหลัก</a>
            <a href="/setup.php" class="btn-secondary" style="display: inline-block; text-decoration: none;">🛠️ Setup Wizard</a>
        </div>
    </div>

    <script>
        async function testAPI(url, testId) {
            const statusEl = document.getElementById(testId + '-status');
            const responseEl = document.getElementById(testId + '-response');
            
            statusEl.innerHTML = '⏳ Testing...';
            responseEl.style.display = 'none';
            
            try {
                const startTime = Date.now();
                const response = await fetch(url);
                const endTime = Date.now();
                const duration = endTime - startTime;
                
                const contentType = response.headers.get('content-type') || 'unknown';
                const responseText = await response.text();
                
                // Try to parse as JSON
                let jsonData = null;
                let isValidJSON = false;
                try {
                    jsonData = JSON.parse(responseText);
                    isValidJSON = true;
                } catch (e) {
                    // Not valid JSON
                }
                
                // Update status
                let statusClass = 'status-success';
                let statusText = '';
                
                if (response.ok && isValidJSON) {
                    statusText = `✅ Success (${response.status}) - ${duration}ms - JSON Valid`;
                } else if (response.ok && !isValidJSON) {
                    statusText = `⚠️ Warning (${response.status}) - ${duration}ms - Not JSON`;
                    statusClass = 'status-warning';
                } else {
                    statusText = `❌ Error (${response.status}) - ${duration}ms - ${response.statusText}`;
                    statusClass = 'status-error';
                }
                
                statusEl.innerHTML = `
                    <div class="${statusClass}">${statusText}</div>
                    <div class="text-sm text-gray-600 mt-1">Content-Type: ${contentType}</div>
                `;
                
                // Show response
                responseEl.textContent = responseText;
                responseEl.style.display = 'block';
                
                // If it's valid JSON, also show formatted version
                if (isValidJSON) {
                    responseEl.textContent = JSON.stringify(jsonData, null, 2);
                }
                
            } catch (error) {
                statusEl.innerHTML = `<div class="status-error">❌ Network Error: ${error.message}</div>`;
                responseEl.textContent = 'Error: ' + error.message;
                responseEl.style.display = 'block';
            }
        }
        
        function clearResult(testId) {
            document.getElementById(testId + '-status').innerHTML = '';
            document.getElementById(testId + '-response').style.display = 'none';
        }
        
        // Auto-test on page load
        window.addEventListener('load', function() {
            setTimeout(() => {
                testAPI('/api/get-materials.php', 'materials');
            }, 500);
        });
    </script>
</body>
</html>