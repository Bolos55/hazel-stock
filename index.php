<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hazel Stock Management</title>
    <style>
        body {
            font-family: 'Sarabun', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #C4161C 0%, #8B0000 100%);
            color: white;
            text-align: center;
            padding: 2rem 1rem;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            display: block;
        }
        .content {
            padding: 2rem 1rem;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #C4161C;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
        }
        .btn:hover {
            background: #8B0000;
        }
        .hidden {
            display: none;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="logo">
            <div>Beverages & Appetizers</div>
            <h1>บันทึกสต็อกวัตถุดิบ</h1>
            <div id="currentDate"></div>
        </div>
        
        <div class="content">
            <div id="errorMessage" class="error hidden"></div>
            
            <!-- Employee Section -->
            <div id="employeeSection" class="card">
                <label for="employeeName">ชื่อพนักงาน</label>
                <input type="text" id="employeeName" class="form-input" placeholder="กรอกชื่อของคุณ">
                <button class="btn" onclick="startRecording()">เริ่มบันทึก</button>
            </div>
            
            <!-- Materials Section -->
            <div id="materialsSection" class="hidden">
                <div class="card">
                    <h3>รายการวัตถุดิบ</h3>
                    <div id="materialsList"></div>
                    <button class="btn" onclick="submitStock()">บันทึกข้อมูล</button>
                </div>
            </div>
            
            <!-- Success Section -->
            <div id="successSection" class="card hidden">
                <div style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
                    <h2>บันทึกสำเร็จ!</h2>
                    <p>ข้อมูลสต็อกได้ถูกบันทึกเรียบร้อยแล้ว</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update current date
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('th-TH');
        
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
                
                if (data.success && data.has_records) {
                    document.getElementById('employeeSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                    return;
                }
                
                // Load materials
                await loadMaterials();
                
            } catch (error) {
                showError('เกิดข้อผิดพลาด: ' + error.message);
            }
        }
        
        // Load materials
        async function loadMaterials() {
            try {
                const response = await fetch('/api/get-materials.php');
                const data = await response.json();
                
                if (data.success) {
                    materials = data.materials || [];
                    displayMaterials();
                    document.getElementById('employeeSection').classList.add('hidden');
                    document.getElementById('materialsSection').classList.remove('hidden');
                } else {
                    showError('ไม่สามารถโหลดรายการวัตถุดิบได้: ' + data.message);
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาดในการโหลดข้อมูล: ' + error.message);
            }
        }
        
        // Display materials
        function displayMaterials() {
            const container = document.getElementById('materialsList');
            container.innerHTML = '';
            
            if (materials.length === 0) {
                container.innerHTML = '<p>ไม่พบรายการวัตถุดิบ</p>';
                return;
            }
            
            materials.forEach(material => {
                const div = document.createElement('div');
                div.style.marginBottom = '1rem';
                div.innerHTML = `
                    <label>${material.material_name} (${material.unit})</label>
                    <input type="number" 
                           class="form-input" 
                           placeholder="จำนวนคงเหลือ"
                           data-material-id="${material.id}"
                           min="0" 
                           step="0.01">
                `;
                container.appendChild(div);
            });
        }
        
        // Submit stock data
        async function submitStock() {
            const inputs = document.querySelectorAll('#materialsList input[type="number"]');
            const stockData = [];
            
            inputs.forEach(input => {
                const quantity = parseFloat(input.value) || 0;
                stockData.push({
                    material_id: input.dataset.materialId,
                    quantity: quantity,
                    photo: 'no-photo.jpg' // Temporary - will add photo feature later
                });
            });
            
            if (stockData.length === 0) {
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
                
                if (data.success) {
                    document.getElementById('materialsSection').classList.add('hidden');
                    document.getElementById('successSection').classList.remove('hidden');
                } else {
                    showError('เกิดข้อผิดพลาด: ' + data.message);
                }
            } catch (error) {
                showError('เกิดข้อผิดพลาดในการบันทึก: ' + error.message);
            }
        }
    </script>
</body>
</html>