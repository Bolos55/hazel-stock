<?php
require_once 'config.php';
require_once 'auth.php';
require_once 'work-date-helper.php';

// Check if user is logged in as admin                                                                                                              
$isAdmin = isLoggedIn() && isAdmin();
$loggedInName = isLoggedIn() ? getEmployeeName() : '';

// Get work date info
$workDateTime = getWorkDateTime();
$isNextDay = $workDateTime['is_next_day'];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#C4161C">
    <title>Hazel Stock Management</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- iOS Splash Screens -->
    <meta name="apple-mobile-web-app-title" content="Hazel Stock">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .hidden {
            display: none !important;
        }
        
        .camera-section {
            border: 2px dashed #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 0.5rem;
        }
        .camera-preview {
            width: 100%;
            max-width: 300px;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
            border: 2px solid #d1d5db;
        }
        .btn-camera, .btn-capture, .btn-retake {
            background: #3b82f6;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
            text-decoration: none;
        }
        .btn-camera:hover, .btn-capture:hover, .btn-retake:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-camera:active, .btn-capture:active, .btn-retake:active {
            transform: translateY(0);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-block;
            text-decoration: none;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        .w-full {
            width: 100%;
        }
        
        .space-y-3 > * + * {
            margin-top: 0.75rem;
        }
        .btn-capture {
            background: #10b981;
            margin-top: 0.5rem;
        }
        .btn-capture:hover {
            background: #059669;
        }
        .btn-retake {
            background: #f59e0b;
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
        }
        .btn-retake:hover {
            background: #d97706;
        }
        .photo-preview {
            margin-top: 1rem;
            padding: 1rem;
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 0.5rem;
        }
        .preview-image {
            width: 100%;
            max-width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 0.375rem;
            border: 1px solid #d1d5db;
        }
        .quantity-input, .quantity-input-main, .quantity-input-sub {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .quantity-input:focus, .quantity-input-main:focus, .quantity-input-sub:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .grid {
            display: grid;
        }
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .gap-3 {
            gap: 0.75rem;
        }
        
        /* Footer Styles */
        .hazel-footer {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1rem 0;
        }
        
        /* Loading Styles */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .btn-loading {
            position: relative;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            margin: auto;
            border: 2px solid transparent;
            border-top-color: #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }
        
        .footer-left {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .footer-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .footer-text h3 {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        
        .footer-text p {
            margin: 0.25rem 0;
            opacity: 0.9;
        }
        
        .footer-tagline {
            font-style: italic;
            font-size: 0.875rem;
            opacity: 0.8;
        }
        
        .footer-right {
            flex: 1;
            max-width: 400px;
        }
        
        .owner-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }
        
        .owner-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .owner-info h4 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .owner-info p {
            margin: 0.25rem 0;
            opacity: 0.9;
        }
        
        .owner-quote {
            font-style: italic;
            font-size: 0.875rem;
            opacity: 0.8;
            margin-top: 0.5rem;
            line-height: 1.4;
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin-top: 2rem;
            padding-top: 1rem;
            text-align: center;
            opacity: 0.8;
        }
        
        .footer-bottom p {
            margin: 0.25rem 0;
            font-size: 0.875rem;
        }
        
        .footer-system {
            opacity: 0.6;
        }
        
        /* Responsive Footer */
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
            
            .footer-left {
                flex-direction: column;
                text-align: center;
            }
            
            .owner-section {
                flex-direction: column;
                text-align: center;
            }
            
            .owner-photo {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>บันทึกสต็อกวัตถุดิบ</h1>
            <div class="current-date" id="currentDate"></div>
        </div>
        
        <div class="employee-section">
            <div id="errorMessage" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>
            
            <!-- Debug Panel - Hidden by default -->
            <div class="material-card mb-4 hidden" id="debugPanel" style="background: #f8f9fa; border: 1px solid #dee2e6;">
                <h4 class="text-sm font-semibold mb-2 text-gray-700">🔧 System Status</h4>
                <div class="text-xs text-gray-600 space-y-1">
                    <div>Server: <span id="serverStatus" class="font-mono">Checking...</span></div>
                    <div>APIs: <span id="apiStatus" class="font-mono">Checking...</span></div>
                    <div>Database: <span id="dbStatus" class="font-mono">Checking...</span></div>
                </div>
                <div class="mt-2">
                    <a href="/setup.php" class="text-xs text-blue-600 hover:text-blue-800">🛠️ Setup Wizard</a>
                    <span class="mx-2 text-gray-400">|</span>
                    <a href="/test-basic.php" class="text-xs text-blue-600 hover:text-blue-800">🔍 System Test</a>
                    <span class="mx-2 text-gray-400">|</span>
                    <a href="/view-records.php" class="text-xs text-blue-600 hover:text-blue-800">📊 ดูข้อมูลสต็อก</a>
                </div>
            </div>
            
            <!-- Quick Links (Admin Only) -->
            <?php if ($isAdmin): ?>
            <div class="material-card mb-4" style="background: #f0f9ff; border: 1px solid #0ea5e9;">
                <h4 class="text-sm font-semibold mb-2 text-blue-700">🔗 เมนูด่วน (แอดมิน)</h4>
                <div class="flex flex-wrap gap-2">
                    <a href="/view-records.php" class="inline-block bg-blue-500 text-black px-3 py-1 rounded text-xs hover:bg-blue-600 hover:text-white font-semibold">📊 ดูข้อมูลสต็อก</a>
                    <a href="/manage-employees.php" class="inline-block bg-green-500 text-black px-3 py-1 rounded text-xs hover:bg-green-600 hover:text-white font-semibold">👥 จัดการพนักงาน</a>
                    <a href="/manage-materials.php" class="inline-block bg-purple-500 text-black px-3 py-1 rounded text-xs hover:bg-purple-600 hover:text-white font-semibold">🧪 จัดการวัตถุดิบ</a>
                    <a href="/add-stock.php" class="inline-block bg-orange-500 text-black px-3 py-1 rounded text-xs hover:bg-orange-600 hover:text-white font-semibold">📦 เพิ่มสต็อกเข้า</a>
                    <a href="/change-password.php" class="inline-block bg-pink-500 text-black px-3 py-1 rounded text-xs hover:bg-pink-600 hover:text-white font-semibold">🔐 เปลี่ยนรหัสผ่าน</a>
                    <a href="/migrate.php" class="inline-block bg-amber-500 text-black px-3 py-1 rounded text-xs hover:bg-amber-600 hover:text-white font-semibold">⚡ อัพเดทระบบ</a>
                    <button onclick="toggleDebug()" class="bg-yellow-500 text-black px-3 py-1 rounded text-xs hover:bg-yellow-600 hover:text-white font-semibold">🔧 System Status</button>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- User Info & Logout (Admin Only) -->
            <?php if ($isAdmin): ?>
            <div class="material-card mb-4" style="background: #f0fdf4; border: 1px solid #10b981;">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-sm text-gray-600">ผู้ใช้งาน:</span>
                        <strong class="text-green-700"><?php echo htmlspecialchars($loggedInName); ?></strong>
                        <span class="ml-2 text-xs bg-red-500 text-white px-2 py-1 rounded">แอดมิน</span>
                    </div>
                    <a href="logout.php" class="text-sm text-red-600 hover:text-red-800 font-semibold">🚪 ออกจากระบบ</a>
                </div>
            </div>
            <?php else: ?>
            <!-- Admin Login Link (For Employees) -->
            <div class="material-card mb-4" style="background: #fef3c7; border: 1px solid #f59e0b;">
                <div class="text-center">
                    <span class="text-sm text-gray-600">คุณเป็นแอดมิน?</span>
                    <a href="login.php" class="ml-2 text-sm text-blue-600 hover:text-blue-800 font-semibold">🔐 เข้าสู่ระบบแอดมิน</a>
                </div>
            </div>
            
            <!-- Install App Button (For Non-Admin) -->
            <div id="installAppBanner" style="display: none;" class="material-card mb-4" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border: none;">
                <div class="text-center">
                    <button onclick="installPWAFromHome()" style="
                        width: 100%;
                        background: transparent;
                        color: white;
                        border: 2px solid white;
                        padding: 1rem;
                        border-radius: 0.75rem;
                        font-size: 1rem;
                        font-weight: 600;
                        cursor: pointer;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 0.5rem;
                    ">
                        <span style="font-size: 1.5rem;">📱</span>
                        <span>ติดตั้งแอปบนมือถือ</span>
                    </button>
                    <p style="color: white; font-size: 0.75rem; margin-top: 0.5rem; opacity: 0.9;">
                        ใช้งานได้เหมือนแอปจริง ไม่ต้องเปิดเบราว์เซอร์
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Work Date Notification (if in next day period) -->
            <?php if ($isNextDay): ?>
            <div class="material-card mb-4" style="background: #fef3c7; border: 2px solid #f59e0b;">
                <div class="text-center">
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🌙</div>
                    <h4 class="text-lg font-semibold text-orange-800 mb-2">ตอนนี้เวลา <?= date('H:i') ?> น.</h4>
                    <p class="text-sm text-orange-700">
                        <strong>ยังนับเป็นวันเมื่อวาน</strong> (<?= date('d/m/Y', strtotime($workDateTime['date'])) ?>)
                    </p>
                    <p class="text-xs text-orange-600 mt-2">
                        💡 ระบบจะรีเซ็ทเป็นวันใหม่ตอนตี 3 (03:00 น.)
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Employee Section -->
            <div id="employeeSection" class="employee-card">
                <h3 class="text-lg font-semibold mb-4 text-center">บันทึกสต็อกวัตถุดิบ</h3>
                <?php if ($isAdmin): ?>
                    <p class="text-center text-gray-600 mb-4">คุณ <strong class="text-red-600"><?php echo htmlspecialchars($loggedInName); ?></strong></p>
                <?php else: ?>
                    <label for="employeeName">ชื่อพนักงาน</label>
                    <input type="text" id="employeeName" class="form-input" placeholder="กรอกชื่อของคุณ">
                <?php endif; ?>
                <button class="btn-primary" onclick="startRecording()">เริ่มบันทึก</button>
            </div>
            
            <!-- Materials Section -->
            <div id="materialsSection" class="hidden">
                <div class="material-card">
                    <h3 class="text-lg font-semibold mb-4 text-center">รายการวัตถุดิบ</h3>
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg text-center">
                        <span>พนักงาน: <strong id="displayEmployeeName" class="text-red-600"></strong></span>
                    </div>
                    <div id="materialsList"></div>
                    <button class="btn-primary mt-4" onclick="submitStock()">บันทึกข้อมูลสต็อก</button>
                </div>
            </div>
            
            <!-- Success Section -->
            <div id="successSection" class="employee-card hidden">
                <div class="text-center">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">✅</div>
                    <h2 class="text-xl font-semibold mb-3 text-green-600">บันทึกสำเร็จ!</h2>
                    <p class="text-gray-600">ข้อมูลสต็อกได้ถูกบันทึกเรียบร้อยแล้ว</p>
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm" id="successInfo"></div>
                    
                    <!-- Share Button -->
                    <div class="mt-4">
                        <button class="btn-primary w-full" onclick="shareSuccess()" style="background: #10b981;">
                            📤 แชร์ผลการบันทึก
                        </button>
                        <p class="text-center text-sm text-gray-600 mt-2">
                            💬 โปรดแชร์เข้ากลุ่ม Messenger
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <a href="/view-records.php" class="btn-primary w-full block text-center" style="text-decoration: none;">
                            📊 ดูข้อมูลที่บันทึก
                        </a>
                        <button class="btn-secondary w-full" onclick="startNew()">
                            🔄 บันทึกใหม่
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <footer class="hazel-footer">
            <div class="footer-content">
                <div class="footer-left">
                    <img src="assets/hazel-logo.png" alt="Hazel" class="footer-logo">
                    <div class="footer-text">
                        <h3>Hazel</h3>
                        <p>Beverages & Appetizers</p>
                        <p class="footer-tagline">คุณภาพในทุกหยด ความอร่อยในทุกคำ</p>
                    </div>
                </div>
                <div class="footer-right">
                    <div class="owner-section">
                        <img src="assets/phuriboss.jpg" alt="Owner" class="owner-photo">
                        <div class="owner-info">
                            <h4>ภูริวัฒน์ โภคสวัสดิ์</h4>
                            <p>ผู้อยู่เบื้องหลังกิจการ</p>
                            <p class="owner-quote">"มุ่งมั่นสร้างสรรค์เครื่องดื่มคุณภาพ<br>เพื่อความสุขของลูกค้าทุกท่าน"</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Hazel Beverages & Appetizers. สงวนลิขสิทธิ์.</p>
                <p class="footer-system">ระบบจัดการสต็อกวัตถุดิบ | พัฒนาโดย Kiro AI</p>
            </div>
        </footer>
    </div>

    <script>
        // Get work date (resets at 3 AM instead of midnight)
        function getWorkDate() {
            const now = new Date();
            const hour = now.getHours();
            
            // If before 3 AM, use yesterday's date
            if (hour < 3) {
                const yesterday = new Date(now);
                yesterday.setDate(yesterday.getDate() - 1);
                return yesterday.toISOString().split('T')[0];
            }
            
            return now.toISOString().split('T')[0];
        }
        
        // Update current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('th-TH', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        let materials = [];
        let employeeName = '<?php echo $isAdmin ? addslashes($loggedInName) : ''; ?>';
        const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        
        // Run system check on page load
        window.addEventListener('load', function() {
            checkSystemStatus();
        });
        
        // Toggle debug panel
        function toggleDebug() {
            const panel = document.getElementById('debugPanel');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                checkSystemStatus();
            } else {
                panel.classList.add('hidden');
            }
        }
        
        // Start new recording
        function startNew() {
            // Reset all sections
            document.getElementById('successSection').classList.add('hidden');
            document.getElementById('materialsSection').classList.add('hidden');
            document.getElementById('employeeSection').classList.remove('hidden');
            
            // Clear form
            if (!isAdmin) {
                document.getElementById('employeeName').value = '';
                employeeName = '';
            }
            document.getElementById('materialsList').innerHTML = '';
            
            // Clear photo storage
            window.photoStorage = {};
            
            // Clear localStorage
            const today = getWorkDate();
            const photoStorageKey = `photoStorage_${today}_${employeeName}`;
            const quantityStorageKey = `quantityStorage_${today}_${employeeName}`;
            localStorage.removeItem(photoStorageKey);
            localStorage.removeItem(quantityStorageKey);
            localStorage.removeItem('lastSuccessData'); // Clear share data
            
            // Reset variables
            materials = [];
            window.lastSuccessData = null;
        }
        
        // Share success result
        async function shareSuccess() {
            // Try to load from localStorage if not in memory
            if (!window.lastSuccessData) {
                const saved = localStorage.getItem('lastSuccessData');
                if (saved) {
                    try {
                        window.lastSuccessData = JSON.parse(saved);
                    } catch (e) {
                        console.error('Error loading success data:', e);
                    }
                }
            }
            
            if (!window.lastSuccessData) {
                showError('ไม่พบข้อมูลที่จะแชร์');
                return;
            }
            
            const data = window.lastSuccessData;
            const shareText = `✅ บันทึกสต็อกวัตถุดิบสำเร็จ!\n\n` +
                `👤 พนักงาน: ${data.employeeName}\n` +
                `📅 วันที่: ${data.date}\n` +
                `📦 จำนวนรายการ: ${data.itemCount} รายการ\n` +
                `📸 รูปภาพ: ${data.photoCount} รูป\n` +
                `⏰ เวลา: ${data.time}\n\n` +
                `🏪 Hazel Beverages & Appetizers`;
            
            // Check if Web Share API is supported
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'บันทึกสต็อกวัตถุดิบ - Hazel',
                        text: shareText,
                        url: window.location.origin + '/view-records.php'
                    });
                    console.log('Share successful');
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error('Share error:', error);
                        // Fallback to copy to clipboard
                        copyToClipboard(shareText);
                    }
                }
            } else {
                // Fallback for browsers that don't support Web Share API
                copyToClipboard(shareText);
            }
        }
        
        // Copy text to clipboard (fallback)
        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('📋 คัดลอกข้อความแล้ว!\nสามารถนำไปแปะในไลน์หรือ Messenger ได้เลย');
                }).catch(err => {
                    console.error('Clipboard error:', err);
                    showTextToCopy(text);
                });
            } else {
                showTextToCopy(text);
            }
        }
        
        // Show text in alert for manual copy
        function showTextToCopy(text) {
            alert('กรุณาคัดลอกข้อความนี้:\n\n' + text);
        }
        
        // Check system status (reduced frequency)
        async function checkSystemStatus() {
            // Skip server test for speed
            document.getElementById('serverStatus').textContent = '✅ Online';
            document.getElementById('serverStatus').className = 'font-mono text-green-600';
            
            // Check APIs (simplified)
            try {
                const response = await fetch('./api/get-materials.php');
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        document.getElementById('apiStatus').textContent = `✅ Working (${data.count || 0} materials)`;
                        document.getElementById('apiStatus').className = 'font-mono text-green-600';
                        document.getElementById('dbStatus').textContent = '✅ Connected';
                        document.getElementById('dbStatus').className = 'font-mono text-green-600';
                    } else {
                        document.getElementById('apiStatus').textContent = '⚠️ API Error';
                        document.getElementById('apiStatus').className = 'font-mono text-yellow-600';
                        document.getElementById('dbStatus').textContent = '❌ ' + (data.message || 'Error');
                        document.getElementById('dbStatus').className = 'font-mono text-red-600';
                    }
                } else {
                    document.getElementById('apiStatus').textContent = '❌ Invalid Response';
                    document.getElementById('apiStatus').className = 'font-mono text-red-600';
                    document.getElementById('dbStatus').textContent = '❌ Connection Failed';
                    document.getElementById('dbStatus').className = 'font-mono text-red-600';
                }
            } catch (error) {
                document.getElementById('apiStatus').textContent = '❌ Failed';
                document.getElementById('apiStatus').className = 'font-mono text-red-600';
                document.getElementById('dbStatus').textContent = '❌ No Connection';
                document.getElementById('dbStatus').className = 'font-mono text-red-600';
            }
        }
        
        // Show error message
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('hidden');
            
            // Scroll to error message
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto hide after 8 seconds
            setTimeout(() => errorDiv.classList.add('hidden'), 8000);
        }
        
        // Start recording
        async function startRecording() {
            // Get employee name from input if not admin
            if (!isAdmin) {
                employeeName = document.getElementById('employeeName').value.trim();
                if (!employeeName) {
                    showError('กรุณากรอกชื่อพนักงาน');
                    return;
                }
            }
            
            if (!employeeName) {
                showError('เกิดข้อผิดพลาด: ไม่พบข้อมูลผู้ใช้');
                return;
            }
            
            try {
                // Check today's record
                const response = await fetch('/api/get-today-record.php');
                const data = await response.json();
                
                console.log('Today record check:', data);
                
                if (data.success && data.has_records) {
                    // Has records - show success section with share button
                    const recordDate = new Date(data.date);
                    const successData = {
                        employeeName: employeeName,
                        date: recordDate.toLocaleDateString('th-TH', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        }),
                        time: recordDate.toLocaleTimeString('th-TH', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }),
                        itemCount: data.total_records || 0,
                        photoCount: data.total_records || 0
                    };
                    
                    // Save to localStorage for sharing
                    window.lastSuccessData = successData;
                    localStorage.setItem('lastSuccessData', JSON.stringify(successData));
                    
                    document.getElementById('employeeSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                    document.getElementById('successInfo').innerHTML = `
                        <div>วันที่: ${successData.date}</div>
                        <div>จำนวนรายการ: ${successData.itemCount} รายการ</div>
                        <div>สถานะ: บันทึกแล้ว</div>
                    `;
                    return;
                }
                
                // Load materials
                await loadMaterials();
                
            } catch (error) {
                console.error('Start recording error:', error);
                showError('เกิดข้อผิดพลาด: ' + error.message);
            }
        }
        
        // Load materials
        async function loadMaterials() {
            try {
                console.log('Loading materials...');
                const response = await fetch('/api/get-materials.php');
                
                // Check if response is ok
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned HTML instead of JSON. Check server logs.');
                }
                
                const data = await response.json();
                console.log('Materials data:', data);
                
                if (data.success) {
                    materials = data.materials || [];
                    displayMaterials();
                    
                    // Load saved photos and quantities after materials are displayed
                    setTimeout(() => {
                        loadPhotosFromStorage();
                        loadQuantitiesFromStorage();
                    }, 100);
                    
                    document.getElementById('employeeSection').classList.add('hidden');
                    document.getElementById('materialsSection').classList.remove('hidden');
                    document.getElementById('displayEmployeeName').textContent = employeeName;
                } else {
                    showError('ไม่สามารถโหลดรายการวัตถุดิบได้: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Load materials error:', error);
                if (error.message.includes('JSON')) {
                    showError('เซิร์ฟเวอร์มีปัญหา กรุณาเข้าไปที่ /setup.php เพื่อแก้ไขปัญหา');
                } else {
                    showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
                }
            }
        }
        
        // Display materials
        function displayMaterials() {
            const container = document.getElementById('materialsList');
            container.innerHTML = '';
            
            if (materials.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500">ไม่พบรายการวัตถุดิบ</p>';
                return;
            }
            
            materials.forEach((material, index) => {
                const div = document.createElement('div');
                div.className = 'mb-6 p-4 bg-gray-50 rounded-lg border';
                
                // Format unit display
                let unitDisplay = '';
                if (material.sub_unit) {
                    unitDisplay = `<span class="text-xs text-gray-600">หน่วยบรรจุ: <strong>${material.unit}</strong> | หน่วยวัด: <strong>${material.sub_unit}</strong></span>`;
                } else {
                    unitDisplay = `<span class="text-xs text-gray-600">หน่วย: <strong>${material.unit}</strong></span>`;
                }
                
                div.innerHTML = `
                    <div class="mb-3">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-base font-semibold text-gray-800">
                                ${material.material_name}
                            </label>
                            <span class="text-xs text-gray-500">${index + 1}/14</span>
                        </div>
                        ${unitDisplay}
                    </div>
                    
                    <!-- Quantity Input -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">จำนวน${material.unit}</label>
                            <input type="number" 
                                   class="quantity-input-main" 
                                   placeholder="เช่น 2"
                                   data-material-id="${material.id}"
                                   min="0" 
                                   step="1"
                                   onchange="saveQuantityToStorage(${material.id})">
                        </div>
                        ${material.sub_unit ? `
                        <div>
                            <label class="text-xs text-gray-600 mb-1 block">จำนวน${material.sub_unit}</label>
                            <input type="number" 
                                   class="quantity-input-sub" 
                                   placeholder="เช่น 1.5"
                                   data-material-id="${material.id}"
                                   min="0" 
                                   step="0.01"
                                   onchange="saveQuantityToStorage(${material.id})">
                        </div>
                        ` : ''}
                    </div>
                    
                    <!-- Camera Section -->
                    <div class="camera-section">
                        <div class="mb-2">
                            <label class="text-sm font-medium text-red-600">📸 ถ่ายรูปยืนยัน (จำเป็น)</label>
                        </div>
                        
                        <!-- Camera Controls -->
                        <div class="mb-3">
                            <!-- Camera Input (with capture) -->
                            <input type="file" 
                                   accept="image/*" 
                                   capture="environment"
                                   class="hidden" 
                                   id="cameraInput-${material.id}"
                                   onchange="handleFileSelect(${material.id}, this)">
                            <button type="button" 
                                    class="btn-camera" 
                                    onclick="document.getElementById('cameraInput-${material.id}').click()">
                                📷 ถ่ายรูป
                            </button>
                            
                            <!-- Gallery Input (without capture) -->
                            <input type="file" 
                                   accept="image/*" 
                                   class="hidden" 
                                   id="galleryInput-${material.id}"
                                   onchange="handleFileSelect(${material.id}, this)">
                            <button type="button" 
                                    class="btn-camera ml-2" 
                                    onclick="document.getElementById('galleryInput-${material.id}').click()">
                                📁 เลือกจากแกลเลอรี่
                            </button>
                        </div>
                        
                        <!-- Photo Preview -->
                        <div id="photoPreview-${material.id}" class="photo-preview hidden">
                            <img id="photoImg-${material.id}" class="preview-image">
                            <div class="mt-2">
                                <button type="button" 
                                        class="btn-retake" 
                                        onclick="retakePhoto(${material.id})">
                                    🔄 ถ่ายใหม่
                                </button>
                                <span class="ml-2 text-green-600 text-sm">✅ รูปพร้อมแล้ว</span>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(div);
            });
        }
        
        // Submit stock data
        async function submitStock() {
            const stockData = [];
            let missingData = [];
            let missingPhotos = [];
            let firstMissingElement = null;
            
            // Loop through each material
            materials.forEach(material => {
                const mainInput = document.querySelector(`.quantity-input-main[data-material-id="${material.id}"]`);
                const subInput = document.querySelector(`.quantity-input-sub[data-material-id="${material.id}"]`);
                
                const mainValue = mainInput ? mainInput.value.trim() : '';
                const subValue = subInput ? subInput.value.trim() : '';
                
                // Check if fields are empty
                const mainEmpty = mainValue === '';
                const subEmpty = material.sub_unit && subValue === '';
                
                if (mainEmpty) {
                    missingData.push({
                        name: material.material_name,
                        field: 'main',
                        element: mainInput
                    });
                    if (!firstMissingElement) firstMissingElement = mainInput;
                }
                
                if (subEmpty) {
                    missingData.push({
                        name: material.material_name,
                        field: 'sub',
                        element: subInput
                    });
                    if (!firstMissingElement) firstMissingElement = subInput;
                }
                
                const mainQty = parseFloat(mainValue) || 0;
                const subQty = parseFloat(subValue) || 0;
                
                // Calculate total quantity
                let totalQuantity = 0;
                if (material.sub_unit) {
                    totalQuantity = subQty;
                    if (mainQty > 0 && subQty === 0) {
                        totalQuantity = mainQty;
                    }
                } else {
                    totalQuantity = mainQty;
                }
                
                const photoData = window.photoStorage && window.photoStorage[material.id];
                
                // Check for missing photo
                if (!photoData) {
                    missingPhotos.push({
                        name: material.material_name,
                        id: material.id
                    });
                }
                
                stockData.push({
                    material_id: material.id,
                    quantity: totalQuantity,
                    quantity_main: mainQty,
                    quantity_sub: subQty,
                    photo: photoData || 'no-photo.jpg'
                });
            });
            
            // Validate all fields are filled
            if (missingData.length > 0) {
                const materialNames = [...new Set(missingData.map(item => item.name))];
                showError(`กรุณากรอกจำนวนให้ครบทุกช่อง (ถ้าไม่มีให้ใส่ 0):\n${materialNames.join(', ')}`);
                
                // Scroll to first missing field and highlight it
                if (firstMissingElement) {
                    firstMissingElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstMissingElement.focus();
                    firstMissingElement.style.border = '2px solid #ef4444';
                    setTimeout(() => {
                        firstMissingElement.style.border = '';
                    }, 3000);
                }
                return;
            }
            
            // Validate all photos are taken
            if (missingPhotos.length > 0) {
                const materialNames = missingPhotos.map(item => item.name);
                showError(`กรุณาถ่ายรูปยืนยันให้ครบทุกรายการ:\n${materialNames.join(', ')}`);
                
                // Scroll to first missing photo
                const firstMissingPhoto = document.getElementById(`photoPreview-${missingPhotos[0].id}`);
                if (firstMissingPhoto) {
                    firstMissingPhoto.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstMissingPhoto.parentElement.style.border = '2px solid #ef4444';
                    setTimeout(() => {
                        firstMissingPhoto.parentElement.style.border = '';
                    }, 3000);
                }
                return;
            }
            
            // Show loading
            const submitBtn = document.querySelector('button[onclick="submitStock()"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = '⏳ กำลังบันทึก...';
            submitBtn.disabled = true;
            
            try {
                console.log('Submitting stock data:', stockData); // Debug log
                console.log('Photos in storage:', Object.keys(window.photoStorage || {}).length);
                
                // Log photo data sizes
                stockData.forEach(item => {
                    if (item.photo && item.photo !== 'no-photo.jpg') {
                        console.log(`Material ${item.material_id} photo size:`, item.photo.length, 'chars');
                    }
                });
                
                const response = await fetch('/api/submit-stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        employee_name: employeeName,
                        stock_data: stockData
                    })
                });
                
                const data = await response.json();
                console.log('Submit response:', data);
                
                if (data.success) {
                    // Store success data for sharing
                    const now = new Date();
                    const successData = {
                        employeeName: employeeName,
                        date: now.toLocaleDateString('th-TH', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        }),
                        time: now.toLocaleTimeString('th-TH', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }),
                        itemCount: stockData.filter(item => item.quantity_main > 0 || item.quantity_sub > 0).length,
                        photoCount: Object.keys(window.photoStorage || {}).length
                    };
                    
                    // Save to both memory and localStorage
                    window.lastSuccessData = successData;
                    localStorage.setItem('lastSuccessData', JSON.stringify(successData));
                    
                    document.getElementById('materialsSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                    document.getElementById('successInfo').innerHTML = `
                        <div>พนักงาน: ${employeeName}</div>
                        <div>จำนวนรายการ: ${successData.itemCount} รายการ</div>
                        <div>รูปภาพ: ${successData.photoCount} รูป</div>
                        <div>เวลาบันทึก: ${successData.date} ${successData.time}</div>
                    `;
                } else {
                    showError('เกิดข้อผิดพลาด: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Submit error:', error);
                showError('เกิดข้อผิดพลาดในการบันทึก: ' + error.message);
            } finally {
                // Restore button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        }
        
        // Photo storage with localStorage persistence
        window.photoStorage = {};
        
        // Load photos from localStorage on page load
        function loadPhotosFromStorage() {
            const today = getWorkDate();
            const storageKey = `photoStorage_${today}_${employeeName}`;
            const savedPhotos = localStorage.getItem(storageKey);
            
            if (savedPhotos) {
                try {
                    const photoData = JSON.parse(savedPhotos);
                    console.log('Loaded photos from storage:', Object.keys(photoData).length);
                    
                    // Filter only valid material IDs
                    const validMaterialIds = materials.map(m => m.id.toString());
                    
                    // Restore photo previews only for existing materials
                    Object.keys(photoData).forEach(materialId => {
                        if (validMaterialIds.includes(materialId.toString())) {
                            window.photoStorage[materialId] = photoData[materialId];
                            showPhotoPreview(materialId, photoData[materialId]);
                        } else {
                            console.log('Skipping photo for deleted material:', materialId);
                        }
                    });
                    
                    // Clean up old material photos from storage
                    const cleanedStorage = {};
                    validMaterialIds.forEach(id => {
                        if (photoData[id]) {
                            cleanedStorage[id] = photoData[id];
                        }
                    });
                    
                    // Update storage with cleaned data
                    if (Object.keys(cleanedStorage).length !== Object.keys(photoData).length) {
                        localStorage.setItem(storageKey, JSON.stringify(cleanedStorage));
                        console.log('Cleaned up old material photos from storage');
                    }
                } catch (e) {
                    console.error('Error loading photos:', e);
                    window.photoStorage = {};
                }
            }
        }
        
        // Save photos to localStorage
        function savePhotosToStorage() {
            const today = getWorkDate();
            const storageKey = `photoStorage_${today}_${employeeName}`;
            
            try {
                localStorage.setItem(storageKey, JSON.stringify(window.photoStorage));
                console.log('Saved photos to storage:', Object.keys(window.photoStorage).length);
            } catch (e) {
                console.error('Error saving photos:', e);
                // If storage is full, try to clear old data
                clearOldPhotoStorage();
            }
        }
        
        // Save quantity to localStorage
        function saveQuantityToStorage(materialId) {
            const today = getWorkDate();
            const storageKey = `quantityStorage_${today}_${employeeName}`;
            
            const mainInput = document.querySelector(`.quantity-input-main[data-material-id="${materialId}"]`);
            const subInput = document.querySelector(`.quantity-input-sub[data-material-id="${materialId}"]`);
            
            // Get current storage
            let quantities = {};
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved) quantities = JSON.parse(saved);
            } catch (e) {
                console.error('Error loading quantities:', e);
            }
            
            // Update quantities
            const mainValue = mainInput ? mainInput.value : '';
            const subValue = subInput ? subInput.value : '';
            
            quantities[materialId] = {
                main: mainValue,
                sub: subValue
            };
            
            console.log('Saving quantity for material', materialId, ':', quantities[materialId]);
            
            // Save back
            try {
                localStorage.setItem(storageKey, JSON.stringify(quantities));
                console.log('Quantity saved to localStorage');
            } catch (e) {
                console.error('Error saving quantities:', e);
            }
        }
        
        // Load quantities from localStorage
        function loadQuantitiesFromStorage() {
            const today = getWorkDate();
            const storageKey = `quantityStorage_${today}_${employeeName}`;
            
            console.log('Loading quantities from storage key:', storageKey);
            
            try {
                const saved = localStorage.getItem(storageKey);
                if (saved) {
                    const quantities = JSON.parse(saved);
                    const validMaterialIds = materials.map(m => m.id.toString());
                    let loadedCount = 0;
                    
                    console.log('Found saved quantities:', quantities);
                    
                    Object.keys(quantities).forEach(materialId => {
                        // Only load quantities for existing materials
                        if (!validMaterialIds.includes(materialId.toString())) {
                            console.log('Skipping quantity for deleted material:', materialId);
                            return;
                        }
                        
                        const data = quantities[materialId];
                        const mainInput = document.querySelector(`.quantity-input-main[data-material-id="${materialId}"]`);
                        const subInput = document.querySelector(`.quantity-input-sub[data-material-id="${materialId}"]`);
                        
                        console.log('Loading quantity for material', materialId, ':', data);
                        
                        if (mainInput && data.main !== undefined && data.main !== '') {
                            mainInput.value = data.main;
                            loadedCount++;
                            console.log('Set main input:', data.main);
                        }
                        if (subInput && data.sub !== undefined && data.sub !== '') {
                            subInput.value = data.sub;
                            loadedCount++;
                            console.log('Set sub input:', data.sub);
                        }
                    });
                    
                    if (loadedCount > 0) {
                        console.log('✅ Loaded quantities from storage:', loadedCount, 'fields');
                    } else {
                        console.log('⚠️ No quantities loaded');
                    }
                    
                    // Clean up old material quantities from storage
                    const cleanedQuantities = {};
                    validMaterialIds.forEach(id => {
                        if (quantities[id]) {
                            cleanedQuantities[id] = quantities[id];
                        }
                    });
                    
                    // Update storage with cleaned data
                    if (Object.keys(cleanedQuantities).length !== Object.keys(quantities).length) {
                        localStorage.setItem(storageKey, JSON.stringify(cleanedQuantities));
                        console.log('Cleaned up old material quantities from storage');
                    }
                } else {
                    console.log('No saved quantities found');
                }
            } catch (e) {
                console.error('Error loading quantities:', e);
            }
        }
        
        // Clear old photo storage (older than 7 days)
        function clearOldPhotoStorage() {
            const today = new Date();
            const keys = Object.keys(localStorage);
            
            keys.forEach(key => {
                if (key.startsWith('photoStorage_')) {
                    const dateStr = key.split('_')[1];
                    const photoDate = new Date(dateStr);
                    const daysDiff = (today - photoDate) / (1000 * 60 * 60 * 24);
                    
                    if (daysDiff > 7) {
                        localStorage.removeItem(key);
                        console.log('Removed old photo storage:', key);
                    }
                }
            });
        }
        
        // Handle file select (both camera and gallery) - optimized
        function handleFileSelect(materialId, input) {
            console.log('File select triggered for material:', materialId);
            const file = input.files[0];
            
            if (!file) {
                console.log('No file selected');
                return;
            }
            
            console.log('File selected:', {
                name: file.name,
                size: file.size,
                type: file.type
            });
            
            // Validate file size (increased to 20MB for high-res photos)
            if (file.size > 20 * 1024 * 1024) {
                showError('ไฟล์รูปใหญ่เกินไป (สูงสุด 20MB)');
                console.error('File too large:', file.size);
                return;
            }
            
            // Validate file type (more permissive)
            if (!file.type.startsWith('image/')) {
                showError('กรุณาเลือกไฟล์รูปภาพ');
                console.error('Invalid file type:', file.type);
                return;
            }
            
            console.log('File validation passed, processing...');
            
            // Show loading with progress
            const preview = document.getElementById(`photoPreview-${materialId}`);
            preview.innerHTML = '<div class="text-center py-4"><div style="font-size: 2rem;">⏳</div><div class="mt-2">กำลังประมวลผลรูป...</div><div class="text-xs text-gray-500 mt-1">กรุณารอสักครู่</div></div>';
            preview.classList.remove('hidden');
            
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log('File read complete, resizing...');
                // Resize image - smaller size for faster processing
                resizeImage(e.target.result, 400, 300, (resizedDataUrl) => {
                    console.log('Image resized, saving...');
                    window.photoStorage[materialId] = resizedDataUrl;
                    showPhotoPreview(materialId, resizedDataUrl);
                    savePhotosToStorage(); // Save to localStorage
                    console.log('Photo saved successfully');
                });
            };
            reader.onerror = function(error) {
                console.error('FileReader error:', error);
                showError('เกิดข้อผิดพลาดในการอ่านไฟล์');
                preview.classList.add('hidden');
            };
            reader.readAsDataURL(file);
            
            // Clear the input so same file can be selected again
            input.value = '';
        }
        
        // Check camera support and show appropriate message
        function checkCameraSupport() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                console.log('Camera not supported on this device/browser');
                // Still allow file input as fallback
            }
        }
        
        // Initialize camera check
        window.addEventListener('load', function() {
            checkCameraSupport();
        });
        
        // Resize image to reduce file size
        function resizeImage(dataUrl, maxWidth, maxHeight, callback) {
            console.log('Starting image resize...');
            const img = new Image();
            img.onload = function() {
                console.log('Image loaded:', img.width, 'x', img.height);
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Calculate new dimensions (much smaller for faster loading)
                let { width, height } = img;
                if (width > height) {
                    if (width > maxWidth) {
                        height = (height * maxWidth) / width;
                        width = maxWidth;
                    }
                } else {
                    if (height > maxHeight) {
                        width = (width * maxHeight) / height;
                        height = maxHeight;
                    }
                }
                
                console.log('Resizing to:', width, 'x', height);
                canvas.width = width;
                canvas.height = height;
                
                // Draw and compress aggressively for speed
                ctx.drawImage(img, 0, 0, width, height);
                const resizedDataUrl = canvas.toDataURL('image/jpeg', 0.5); // Reduced to 50% quality for speed
                console.log('Resize complete, data URL length:', resizedDataUrl.length);
                callback(resizedDataUrl);
            };
            img.onerror = function(error) {
                console.error('Image load error:', error);
                showError('เกิดข้อผิดพลาดในการประมวลผลรูป');
            };
            img.src = dataUrl;
        }
        
        // Show photo preview
        function showPhotoPreview(materialId, dataUrl) {
            const preview = document.getElementById(`photoPreview-${materialId}`);
            const img = document.getElementById(`photoImg-${materialId}`);
            
            if (!preview || !img) {
                // Silently skip if elements not found (material might be deleted)
                return;
            }
            
            img.src = dataUrl;
            preview.classList.remove('hidden');
            console.log('Photo preview displayed for material:', materialId);
        }
        
        // Retake photo
        function retakePhoto(materialId) {
            delete window.photoStorage[materialId];
            document.getElementById(`photoPreview-${materialId}`).classList.add('hidden');
            savePhotosToStorage(); // Update localStorage
        }
    </script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        // Register Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker registered:', registration.scope);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            console.log('[PWA] New Service Worker found');
                            
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New version available
                                    if (confirm('🎉 มีเวอร์ชันใหม่! ต้องการอัพเดทหรือไม่?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('[PWA] Service Worker registration failed:', error);
                    });
                
                // Handle controller change (new SW activated)
                navigator.serviceWorker.addEventListener('controllerchange', () => {
                    console.log('[PWA] New Service Worker activated');
                });
            });
        } else {
            console.log('[PWA] Service Workers not supported');
        }
        
        // Check if running as PWA
        function isPWA() {
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true;
        }
        
        if (isPWA()) {
            console.log('[PWA] Running as installed app');
            document.body.classList.add('pwa-mode');
        }
        
        // Install PWA from home page
        let homeDeferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            homeDeferredPrompt = e;
            
            // Show install banner if not installed
            if (!isPWA()) {
                document.getElementById('installAppBanner').style.display = 'block';
            }
        });
        
        async function installPWAFromHome() {
            if (!homeDeferredPrompt) {
                alert('ไม่สามารถติดตั้งได้ในขณะนี้\nกรุณาลองใหม่อีกครั้ง');
                return;
            }
            
            homeDeferredPrompt.prompt();
            const { outcome } = await homeDeferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted installation');
                document.getElementById('installAppBanner').style.display = 'none';
                
                // Show success message
                alert('✅ ติดตั้งสำเร็จ!\nตรวจสอบไอคอนบนหน้าจอหลักของคุณ');
            }
            
            homeDeferredPrompt = null;
        }
    </script>
    
    <!-- PWA Install Prompt -->
    <script src="/pwa-install.js"></script>
</body>
</html>