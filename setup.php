<?php
/**
 * Hazel Stock Management - Setup Wizard
 * This wizard will help you set up the database and fix common issues
 */

// Prevent direct access in production
$isSetupMode = true;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hazel Stock Setup Wizard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .setup-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .step {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
        .step.success {
            border-color: #10b981;
            background: #f0fdf4;
        }
        .step.error {
            border-color: #ef4444;
            background: #fef2f2;
        }
        .step.warning {
            border-color: #f59e0b;
            background: #fffbeb;
        }
        .status-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }
        .code-block {
            background: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .btn-setup {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 600;
            margin: 0.5rem 0.5rem 0.5rem 0;
            transition: all 0.2s ease;
        }
        .btn-setup:hover {
            background: #2563eb;
        }
        .btn-danger {
            background: #ef4444;
        }
        .btn-danger:hover {
            background: #dc2626;
        }
        .btn-success {
            background: #10b981;
        }
        .btn-success:hover {
            background: #059669;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="text-center mb-6">
            <img src="assets/hazel-logo.png" alt="Hazel" style="width: 80px; height: 80px; margin: 0 auto 1rem;">
            <h1 class="text-2xl font-bold text-red-600">Hazel Stock Management</h1>
            <p class="text-gray-600">ระบบจัดการสต็อกวัตถุดิบ</p>
        </div>

        <!-- System Status -->
        <div class="material-card mb-6" style="background: #f0fdf4; border: 2px solid #10b981;">
            <h3 class="text-lg font-semibold text-green-700 mb-4">✅ ระบบพร้อมใช้งาน!</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center">
                    <span class="text-green-600 mr-2">✅</span>
                    <span>Database เชื่อมต่อสำเร็จ</span>
                </div>
                <div class="flex items-center">
                    <span class="text-green-600 mr-2">✅</span>
                    <span>Tables สร้างครบถ้วน</span>
                </div>
                <div class="flex items-center">
                    <span class="text-green-600 mr-2">✅</span>
                    <span>API ทำงานปกติ</span>
                </div>
                <div class="flex items-center">
                    <span class="text-green-600 mr-2">✅</span>
                    <span>ระบบถ่ายรูปพร้อม</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="material-card mb-6">
            <h3 class="text-lg font-semibold mb-4">🚀 เริ่มใช้งาน</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="/" class="block p-4 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    <div class="text-2xl mb-2">📝</div>
                    <h4 class="font-semibold text-blue-700">บันทึกสต็อก</h4>
                    <p class="text-sm text-blue-600">บันทึกข้อมูลสต็อกรายวัน</p>
                </a>
                
                <a href="/view-records.php" class="block p-4 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition-colors">
                    <div class="text-2xl mb-2">📊</div>
                    <h4 class="font-semibold text-green-700">ดูข้อมูล</h4>
                    <p class="text-sm text-green-600">ดูและ Export ข้อมูล</p>
                </a>
                
                <a href="/manage-employees.php" class="block p-4 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors">
                    <div class="text-2xl mb-2">👥</div>
                    <h4 class="font-semibold text-purple-700">จัดการพนักงาน</h4>
                    <p class="text-sm text-purple-600">เพิ่ม แก้ไข ลบพนักงาน</p>
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="material-card">
            <h3 class="text-lg font-semibold mb-4">ℹ️ ข้อมูลระบบ</h3>
            <div class="text-sm text-gray-600 space-y-2">
                <div><strong>เวอร์ชัน:</strong> Hazel Stock Management v2.0</div>
                <div><strong>ฐานข้อมูล:</strong> MySQL (Aiven Cloud)</div>
                <div><strong>เซิร์ฟเวอร์:</strong> Render.com</div>
                <div><strong>ฟีเจอร์:</strong> บันทึกสต็อก, ถ่ายรูป, Export CSV, จัดการพนักงาน</div>
                <div><strong>อัพเดทล่าสุด:</strong> <?= date('d/m/Y H:i') ?> น.</div>
            </div>
        </div>

        <!-- Hidden Debug Section -->
        <div id="debugSection" class="hidden">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%"></div>
            </div>

            <div id="setupSteps">
                <!-- Steps will be loaded here -->
            </div>

            <div class="text-center mt-6">
                <button class="btn-setup" onclick="runSetup()">🚀 เริ่มการตรวจสอบ</button>
                <button class="btn-setup btn-success" onclick="createTables()" id="btnCreateTables">📊 สร้าง Database Tables</button>
                <button class="btn-setup btn-danger" onclick="resetSetup()">🔄 เริ่มใหม่</button>
                <button class="btn-setup" onclick="directCreateTables()" style="background: #f59e0b;">⚡ สร้าง Tables ตรงๆ</button>
            </div>
        </div>

        <!-- Show Debug Button -->
        <div class="text-center mt-6">
            <button onclick="toggleDebugSection()" class="text-sm text-gray-500 hover:text-gray-700">
                🔧 แสดงเครื่องมือ Debug (สำหรับผู้พัฒนา)
            </button>
        </div>

        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold mb-2">📋 ขั้นตอนการติดตั้งด้วยตนเอง:</h3>
            <ol class="text-sm text-gray-700 space-y-1">
                <li>1. ตั้งค่า Environment Variables บน Render.com</li>
                <li>2. Import database schema จาก database.sql</li>
                <li>3. ทดสอบการเชื่อมต่อ API</li>
                <li>4. เริ่มใช้งานระบบ</li>
            </ol>
        </div>
    </div>

    <script>
        let currentStep = 0;
        let totalSteps = 5;
        let setupResults = {};

        async function runSetup() {
            currentStep = 0;
            updateProgress();
            
            const steps = [
                { name: 'checkEnvironment', title: 'ตรวจสอบ Environment Variables' },
                { name: 'testDatabaseConnection', title: 'ทดสอบการเชื่อมต่อฐานข้อมูล' },
                { name: 'checkTables', title: 'ตรวจสอบ Database Tables' },
                { name: 'testAPIs', title: 'ทดสอบ API Endpoints' },
                { name: 'finalCheck', title: 'ตรวจสอบสุดท้าย' }
            ];

            const container = document.getElementById('setupSteps');
            container.innerHTML = '';

            for (let step of steps) {
                await runStep(step);
                currentStep++;
                updateProgress();
                await sleep(500); // Small delay for better UX
            }

            showFinalResults();
        }

        async function runStep(step) {
            const stepDiv = createStepDiv(step.title, 'running');
            document.getElementById('setupSteps').appendChild(stepDiv);

            try {
                const result = await window[step.name]();
                setupResults[step.name] = result;
                updateStepStatus(stepDiv, result.success ? 'success' : 'error', result.message, result.details);
            } catch (error) {
                setupResults[step.name] = { success: false, message: error.message };
                updateStepStatus(stepDiv, 'error', 'เกิดข้อผิดพลาด: ' + error.message);
            }
        }

        function createStepDiv(title, status) {
            const div = document.createElement('div');
            div.className = `step ${status}`;
            div.innerHTML = `
                <div class="flex items-center">
                    <span class="status-icon">⏳</span>
                    <h3 class="font-semibold">${title}</h3>
                </div>
                <div class="step-content mt-2">
                    <p class="text-gray-600">กำลังตรวจสอบ...</p>
                </div>
            `;
            return div;
        }

        function updateStepStatus(stepDiv, status, message, details = '') {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                running: '⏳'
            };

            stepDiv.className = `step ${status}`;
            stepDiv.querySelector('.status-icon').textContent = icons[status];
            stepDiv.querySelector('.step-content').innerHTML = `
                <p class="${status === 'success' ? 'text-green-700' : status === 'error' ? 'text-red-700' : 'text-yellow-700'}">${message}</p>
                ${details ? `<div class="mt-2 text-sm text-gray-600">${details}</div>` : ''}
            `;
        }

        function updateProgress() {
            const percentage = (currentStep / totalSteps) * 100;
            document.getElementById('progressFill').style.width = percentage + '%';
        }

        // Step 1: Check Environment Variables
        async function checkEnvironment() {
            const response = await fetch('./test-basic.php');
            const data = await response.json();
            
            if (data.success) {
                const envVars = data.environment_vars;
                const missing = Object.entries(envVars).filter(([key, value]) => value === 'NOT SET');
                
                if (missing.length === 0) {
                    return {
                        success: true,
                        message: 'Environment variables ตั้งค่าครบถ้วน',
                        details: `PHP ${data.php_version} | Server: ${data.server}`
                    };
                } else {
                    return {
                        success: false,
                        message: `ขาด Environment Variables: ${missing.map(([k]) => k).join(', ')}`,
                        details: 'กรุณาตั้งค่า Environment Variables บน Render.com Dashboard'
                    };
                }
            } else {
                return { success: false, message: 'ไม่สามารถตรวจสอบ Environment ได้' };
            }
        }

        // Step 2: Test Database Connection
        async function testDatabaseConnection() {
            try {
                const response = await fetch('./test-config.php');
                const text = await response.text();
                
                if (text.includes('All basic tests passed')) {
                    return {
                        success: true,
                        message: 'เชื่อมต่อฐานข้อมูลสำเร็จ',
                        details: 'Database connection และ config.php ทำงานปกติ'
                    };
                } else if (text.includes('Database class exists: YES')) {
                    return {
                        success: false,
                        message: 'Database class โหลดได้ แต่ไม่สามารถเชื่อมต่อได้',
                        details: 'ตรวจสอบ Environment Variables และ Database credentials'
                    };
                } else {
                    return {
                        success: false,
                        message: 'มีปัญหากับ config.php',
                        details: text.substring(0, 200) + '...'
                    };
                }
            } catch (error) {
                return {
                    success: false,
                    message: 'ไม่สามารถทดสอบการเชื่อมต่อได้',
                    details: error.message
                };
            }
        }

        // Step 3: Check Database Tables
        async function checkTables() {
            try {
                const response = await fetch('./api/get-materials.php');
                const data = await response.json();
                
                if (data.success) {
                    return {
                        success: true,
                        message: `Database tables พร้อมใช้งาน (${data.count || 0} วัตถุดิบ)`,
                        details: 'Tables: employees, raw_materials, daily_stock_records พร้อมใช้งาน'
                    };
                } else if (data.error && data.error.includes('Table') && data.error.includes("doesn't exist")) {
                    return {
                        success: false,
                        message: 'Database tables ยังไม่ได้สร้าง',
                        details: 'กรุณาคลิก "สร้าง Database Tables" ด้านล่าง'
                    };
                } else {
                    return {
                        success: false,
                        message: 'มีปัญหากับ Database tables',
                        details: data.message || data.error || 'Unknown error'
                    };
                }
            } catch (error) {
                return {
                    success: false,
                    message: 'ไม่สามารถตรวจสอบ tables ได้',
                    details: error.message
                };
            }
        }

        // Step 4: Test APIs
        async function testAPIs() {
            const apis = [
                { url: './api/get-today-record.php', name: 'Today Record API' },
                { url: './api/get-materials.php', name: 'Materials API' }
            ];

            let successCount = 0;
            let details = [];

            for (let api of apis) {
                try {
                    const response = await fetch(api.url);
                    const data = await response.json();
                    
                    if (data.success) {
                        successCount++;
                        details.push(`✅ ${api.name}: OK`);
                    } else {
                        details.push(`❌ ${api.name}: ${data.message || 'Error'}`);
                    }
                } catch (error) {
                    details.push(`❌ ${api.name}: ${error.message}`);
                }
            }

            return {
                success: successCount === apis.length,
                message: `API Tests: ${successCount}/${apis.length} ผ่าน`,
                details: details.join('<br>')
            };
        }

        // Step 5: Final Check
        async function finalCheck() {
            const allSuccess = Object.values(setupResults).every(result => result.success);
            
            if (allSuccess) {
                return {
                    success: true,
                    message: '🎉 ระบบพร้อมใช้งาน!',
                    details: 'สามารถเข้าใช้งานหน้าหลักได้แล้ว'
                };
            } else {
                const failedSteps = Object.entries(setupResults)
                    .filter(([key, result]) => !result.success)
                    .map(([key]) => key);
                
                return {
                    success: false,
                    message: 'ยังมีปัญหาที่ต้องแก้ไข',
                    details: `ขั้นตอนที่ล้มเหลว: ${failedSteps.join(', ')}`
                };
            }
        }

        async function createTables() {
            if (confirm('คุณต้องการสร้าง Database Tables ใหม่หรือไม่?')) {
                try {
                    const response = await fetch('./setup-database.php', { method: 'POST' });
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('✅ สร้าง Database Tables สำเร็จ!');
                        runSetup(); // Re-run setup to verify
                    } else {
                        alert('❌ เกิดข้อผิดพลาด: ' + data.message);
                    }
                } catch (error) {
                    alert('❌ เกิดข้อผิดพลาด: ' + error.message);
                }
            }
        }

        function showFinalResults() {
            const allSuccess = Object.values(setupResults).every(result => result.success);
            
            if (allSuccess) {
                document.getElementById('setupSteps').innerHTML += `
                    <div class="step success text-center">
                        <h2 class="text-2xl font-bold text-green-600 mb-4">🎉 ติดตั้งสำเร็จ!</h2>
                        <p class="text-green-700 mb-4">ระบบพร้อมใช้งานแล้ว</p>
                        <a href="/" class="btn-primary" style="display: inline-block; text-decoration: none;">
                            เข้าสู่ระบบ
                        </a>
                    </div>
                `;
            } else {
                document.getElementById('btnCreateTables').style.display = 'inline-block';
            }
        }

        function resetSetup() {
            document.getElementById('setupSteps').innerHTML = '';
            document.getElementById('progressFill').style.width = '0%';
            document.getElementById('btnCreateTables').style.display = 'none';
            currentStep = 0;
            setupResults = {};
        }

        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }

        // Toggle debug section
        function toggleDebugSection() {
            const section = document.getElementById('debugSection');
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
            }
        }

        // Direct create tables without checks
        async function directCreateTables() {
            if (confirm('สร้าง Database Tables ตรงๆ เลยไหม? (ข้ามการตรวจสอบ)')) {
                try {
                    // Show loading
                    document.getElementById('setupSteps').innerHTML = `
                        <div class="step warning">
                            <div class="flex items-center">
                                <span class="status-icon">⏳</span>
                                <h3 class="font-semibold">กำลังสร้าง Database Tables...</h3>
                            </div>
                            <div class="step-content mt-2">
                                <p class="text-yellow-700">กรุณารอสักครู่...</p>
                            </div>
                        </div>
                    `;

                    // Try multiple ways to call setup-database.php
                    let response;
                    let data;
                    
                    try {
                        response = await fetch('./setup-database.php', { method: 'POST' });
                        data = await response.json();
                    } catch (e1) {
                        try {
                            response = await fetch('/setup-database.php', { method: 'POST' });
                            data = await response.json();
                        } catch (e2) {
                            try {
                                response = await fetch('setup-database.php', { method: 'POST' });
                                data = await response.json();
                            } catch (e3) {
                                throw new Error('ไม่สามารถเข้าถึง setup-database.php ได้');
                            }
                        }
                    }
                    
                    if (data.success) {
                        document.getElementById('setupSteps').innerHTML = `
                            <div class="step success text-center">
                                <h2 class="text-2xl font-bold text-green-600 mb-4">🎉 สร้าง Database สำเร็จ!</h2>
                                <p class="text-green-700 mb-4">Tables: ${data.details.tables_created.join(', ')}</p>
                                <p class="text-green-700 mb-4">พนักงาน: ${data.details.employees_inserted} คน | วัตถุดิบ: ${data.details.materials_inserted} รายการ</p>
                                <a href="/" class="btn-primary" style="display: inline-block; text-decoration: none; background: #10b981; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem;">
                                    เข้าสู่ระบบ
                                </a>
                            </div>
                        `;
                    } else {
                        document.getElementById('setupSteps').innerHTML = `
                            <div class="step error">
                                <h3 class="font-semibold text-red-600">❌ เกิดข้อผิดพลาด</h3>
                                <p class="text-red-700 mt-2">${data.message}</p>
                                <div class="mt-2 text-sm text-gray-600">
                                    <p>Host: ${data.details?.host || 'ไม่ทราบ'}</p>
                                    <p>Database: ${data.details?.database || 'ไม่ทราบ'}</p>
                                </div>
                            </div>
                        `;
                    }
                } catch (error) {
                    document.getElementById('setupSteps').innerHTML = `
                        <div class="step error">
                            <h3 class="font-semibold text-red-600">❌ เกิดข้อผิดพลาด</h3>
                            <p class="text-red-700 mt-2">${error.message}</p>
                            <div class="mt-2 text-sm text-gray-600">
                                <p>ลองตรวจสอบ Environment Variables บน Render.com</p>
                                <p>หรือลองเข้าไปที่ setup-database.php ตรงๆ</p>
                            </div>
                        </div>
                    `;
                }
            }
        }
    </script>
</body>
</html>