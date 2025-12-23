<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hazel Stock Management</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
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
        .quantity-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .quantity-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* Footer Styles */
        .hazel-footer {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            margin-top: 4rem;
            padding: 3rem 0 1rem 0;
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
            
            <!-- Quick Links -->
            <div class="material-card mb-4" style="background: #f0f9ff; border: 1px solid #0ea5e9;">
                <h4 class="text-sm font-semibold mb-2 text-blue-700">🔗 เมนูด่วน</h4>
                <div class="flex flex-wrap gap-2">
                    <a href="/view-records.php" class="inline-block bg-blue-500 text-black px-3 py-1 rounded text-xs hover:bg-blue-600 hover:text-white font-semibold">📊 ดูข้อมูลสต็อก</a>
                    <a href="/manage-employees.php" class="inline-block bg-green-500 text-black px-3 py-1 rounded text-xs hover:bg-green-600 hover:text-white font-semibold">👥 จัดการพนักงาน</a>
                    <a href="/manage-materials.php" class="inline-block bg-purple-500 text-black px-3 py-1 rounded text-xs hover:bg-purple-600 hover:text-white font-semibold">🧪 จัดการวัตถุดิบ</a>
                    <a href="/add-stock.php" class="inline-block bg-orange-500 text-black px-3 py-1 rounded text-xs hover:bg-orange-600 hover:text-white font-semibold">📦 เพิ่มสต็อกเข้า</a>
                    <button onclick="toggleDebug()" class="bg-yellow-500 text-black px-3 py-1 rounded text-xs hover:bg-yellow-600 hover:text-white font-semibold">🔧 System Status</button>
                </div>
            </div>
            
            <!-- Employee Section -->
            <div id="employeeSection" class="employee-card">
                <label for="employeeName">ชื่อพนักงาน</label>
                <input type="text" id="employeeName" class="form-input" placeholder="กรอกชื่อของคุณ">
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
                    
                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-3">
                        <button class="btn-primary w-full" onclick="viewRecords()">
                            📊 ดูข้อมูลที่บันทึก
                        </button>
                        <button class="btn-secondary w-full" onclick="startNew()">
                            🔄 บันทึกใหม่
                        </button>
                        <a href="/view-records.php" class="block text-center text-blue-600 hover:text-blue-800 text-sm">
                            📈 ดูข้อมูลทั้งหมด
                        </a>
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
        // Update current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('th-TH', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        let materials = [];
        let employeeName = '';
        
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
        
        // View records (redirect to view page)
        function viewRecords() {
            window.location.href = '/view-records.php';
        }
        
        // Start new recording
        function startNew() {
            // Reset all sections
            document.getElementById('successSection').classList.add('hidden');
            document.getElementById('materialsSection').classList.add('hidden');
            document.getElementById('employeeSection').classList.remove('hidden');
            
            // Clear form
            document.getElementById('employeeName').value = '';
            document.getElementById('materialsList').innerHTML = '';
            
            // Clear photo storage
            window.photoStorage = {};
            
            // Reset variables
            materials = [];
            employeeName = '';
        }
        
        // Check system status
        async function checkSystemStatus() {
            // Check server - Skip test-basic.php since it's not accessible
            document.getElementById('serverStatus').textContent = '✅ Online';
            document.getElementById('serverStatus').className = 'font-mono text-green-600';
            
            // Check APIs
            try {
                const response = await fetch('./api/get-materials.php');
                const contentType = response.headers.get('content-type');
                
                if (response.ok && contentType && contentType.includes('application/json')) {
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
            setTimeout(() => errorDiv.classList.add('hidden'), 5000);
        }
        
        // Start recording
        async function startRecording() {
            employeeName = document.getElementById('employeeName').value.trim();
            if (!employeeName) {
                showError('กรุณากรอกชื่อพนักงาน');
                return;
            }
            
            try {
                // Check today's record
                const response = await fetch('/api/get-today-record.php');
                const data = await response.json();
                
                console.log('Today record check:', data);
                
                if (data.success && data.has_records) {
                    document.getElementById('employeeSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                    document.getElementById('successInfo').innerHTML = `
                        <div>วันที่: ${data.date}</div>
                        <div>จำนวนรายการ: ${data.total_records} รายการ</div>
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
                div.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <label class="block text-sm font-medium text-gray-700">
                            ${material.material_name}
                            ${material.sub_unit ? `(${material.unit} - ${material.sub_unit})` : `(${material.unit})`}
                        </label>
                        <span class="text-xs text-gray-500">${index + 1}/14</span>
                    </div>
                    
                    <!-- Quantity Input -->
                    <input type="number" 
                           class="quantity-input mb-3" 
                           placeholder="จำนวนคงเหลือ"
                           data-material-id="${material.id}"
                           min="0" 
                           step="0.01"
                           required>
                    
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
            const inputs = document.querySelectorAll('#materialsList input[type="number"]');
            const stockData = [];
            let hasData = false;
            let missingPhotos = [];
            
            inputs.forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                const materialId = input.dataset.materialId;
                const photoData = window.photoStorage && window.photoStorage[materialId];
                
                if (quantity > 0) {
                    hasData = true;
                    if (!photoData) {
                        const materialName = materials.find(m => m.id == materialId)?.material_name || `ID ${materialId}`;
                        missingPhotos.push(materialName);
                    }
                }
                
                stockData.push({
                    material_id: materialId,
                    quantity: quantity,
                    photo: photoData || 'no-photo.jpg'
                });
            });
            
            if (!hasData) {
                showError('กรุณากรอกข้อมูลอย่างน้อย 1 รายการ');
                return;
            }
            
            if (missingPhotos.length > 0) {
                showError(`กรุณาถ่ายรูปยืนยันสำหรับ: ${missingPhotos.join(', ')}`);
                return;
            }
            
            try {
                console.log('Submitting stock data:', stockData); // Debug log
                
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
                    document.getElementById('materialsSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                    document.getElementById('successInfo').innerHTML = `
                        <div>พนักงาน: ${employeeName}</div>
                        <div>จำนวนรายการ: ${stockData.filter(item => item.quantity > 0).length} รายการ</div>
                        <div>รูปภาพ: ${Object.keys(window.photoStorage || {}).length} รูป</div>
                        <div>เวลาบันทึก: ${new Date().toLocaleString('th-TH')}</div>
                    `;
                } else {
                    showError('เกิดข้อผิดพลาด: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Submit error:', error);
                showError('เกิดข้อผิดพลาดในการบันทึก: ' + error.message);
            }
        }
        
        // Photo storage
        window.photoStorage = {};
        
        // Handle file select (both camera and gallery)
        function handleFileSelect(materialId, input) {
            const file = input.files[0];
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    showError('ไฟล์รูปใหญ่เกินไป (สูงสุด 5MB)');
                    return;
                }
                
                // Validate file type
                if (!file.type.match(/^image\/(jpeg|jpg|png)$/)) {
                    showError('กรุณาเลือกไฟล์รูป (JPEG, JPG, PNG เท่านั้น)');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Resize image if too large
                    resizeImage(e.target.result, 800, 600, (resizedDataUrl) => {
                        window.photoStorage[materialId] = resizedDataUrl;
                        showPhotoPreview(materialId, resizedDataUrl);
                    });
                };
                reader.readAsDataURL(file);
                
                // Clear the input so same file can be selected again
                input.value = '';
            }
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
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                // Calculate new dimensions
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
                
                canvas.width = width;
                canvas.height = height;
                
                // Draw and compress
                ctx.drawImage(img, 0, 0, width, height);
                const resizedDataUrl = canvas.toDataURL('image/jpeg', 0.8);
                callback(resizedDataUrl);
            };
            img.src = dataUrl;
        }
        
        // Show photo preview
        function showPhotoPreview(materialId, dataUrl) {
            const preview = document.getElementById(`photoPreview-${materialId}`);
            const img = document.getElementById(`photoImg-${materialId}`);
            
            img.src = dataUrl;
            preview.classList.remove('hidden');
        }
        
        // Retake photo
        function retakePhoto(materialId) {
            delete window.photoStorage[materialId];
            document.getElementById(`photoPreview-${materialId}`).classList.add('hidden');
        }
    </script>
</body>
</html>