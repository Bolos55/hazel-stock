<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hazel Stock Management</title>
    <link rel="stylesheet" href="css/style.css">
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
                </div>
            </div>
        </div>
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
                const response = await fetch('/api/get-materials.php');
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
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
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
                div.className = 'mb-4 p-4 bg-gray-50 rounded-lg';
                div.innerHTML = `
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ${material.material_name} (${material.unit})
                    </label>
                    <input type="number" 
                           class="quantity-input" 
                           placeholder="จำนวนคงเหลือ"
                           data-material-id="${material.id}"
                           min="0" 
                           step="0.01"
                           required>
                `;
                container.appendChild(div);
            });
        }
        
        // Submit stock data
        async function submitStock() {
            const inputs = document.querySelectorAll('#materialsList input[type="number"]');
            const stockData = [];
            let hasData = false;
            
            inputs.forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                if (quantity > 0) hasData = true;
                stockData.push({
                    material_id: input.dataset.materialId,
                    quantity: quantity,
                    photo: 'no-photo.jpg' // Temporary - will add photo feature later
                });
            });
            
            if (!hasData) {
                showError('กรุณากรอกข้อมูลอย่างน้อย 1 รายการ');
                return;
            }
            
            try {
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
    </script>
</body>
</html>