<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>บันทึกสต็อกวัตถุดิบ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Header -->
        <header class="app-header hazel-header">
            <img src="assets/hazel-logo.png" alt="Hazel" class="hazel-logo">
            <div class="hazel-subtitle">Beverages & Appetizers</div>
            <h1>บันทึกสต็อกวัตถุดิบ</h1>
            <div class="current-date" id="currentDate"></div>
        </header>


        <!-- Employee Info Section -->
        <section class="employee-section" id="employeeSection">
            <div class="employee-card">
                <label for="employeeName">ชื่อพนักงาน</label>
                <input type="text" id="employeeName" placeholder="กรอกชื่อของคุณ" required class="form-input">
                <button class="btn-primary" id="btnStartRecord">เริ่มบันทึก</button>
            </div>
        </section>

        <!-- Stock Recording Section -->
        <section class="materials-section hidden" id="stockSection">
            <div class="bg-white py-4 px-4 text-center border-b">
                <span>พนักงาน: <strong id="displayEmployeeName"></strong></span>
            </div>

            <!-- Progress Indicator -->
            <div class="material-card mb-4">
                <div class="text-center mb-3">
                    <span>ความคืบหน้า: <strong id="progressText">0/0</strong></span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="bg-red-600 h-3 rounded-full transition-all duration-300" id="progressFill" style="width: 0%"></div>
                </div>
            </div>

            <!-- Stock Items List -->
            <div id="stockList">
                <!-- Items will be loaded dynamically -->
            </div>

            <!-- Submit Button -->
            <div class="p-4">
                <button class="btn-primary" id="btnSubmit" disabled>
                    ส่งข้อมูลสต็อกวันนี้
                </button>
            </div>
        </section>

        <!-- Already Submitted Message -->
        <section class="employee-section text-center hidden" id="submittedSection">
            <div class="employee-card">
                <div class="text-center mb-4">✅</div>
                <h2 class="text-xl text-center mb-3 font-semibold">บันทึกสต็อกวันนี้แล้ว</h2>
                <p class="text-center mb-4">ข้อมูลถูกบันทึกเรียบร้อย ไม่สามารถแก้ไขได้</p>
                <div class="bg-gray-100 p-4 rounded text-sm" id="submittedInfo"></div>
            </div>
        </section>

        <!-- Loading Overlay -->
        <div class="camera-modal hidden" id="loadingOverlay">
            <div class="text-center text-white">
                <div class="mb-4">⏳</div>
                <p>กำลังประมวลผล...</p>
            </div>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="camera-modal hidden" id="cameraModal">
        <div class="camera-container">
            <div class="bg-red-600 p-4 flex justify-between items-center text-white rounded-t-lg">
                <h3 id="cameraTitle">ถ่ายรูปวัตถุดิบ</h3>
                <button class="text-white text-xl" id="btnCloseCamera">✕</button>
            </div>
            <div class="p-4">
                <video id="cameraVideo" autoplay playsinline class="w-full rounded"></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
                <img id="capturedImage" class="w-full rounded hidden">
            </div>
            <div class="camera-controls">
                <button class="bg-orange-500 text-white" id="btnCapture">📷 ถ่ายรูป</button>
                <button class="bg-red-500 text-white hidden" id="btnRetake">🔄 ถ่ายใหม่</button>
                <button class="bg-green-600 text-white hidden" id="btnConfirm">✓ ใช้รูปนี้</button>
            </div>
        </div>
    </div>

    <footer class="bg-gray-50 border-t p-4 text-center">
        <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
            <img src="assets/phuriboss.jpg" alt="PHURIBOSS" class="w-6 h-6 rounded-full">
            <span>© <span id="year"></span> Created by <strong>PHURIBOSS</strong></span>
        </div>
    </footer>

    <script src="js/app.js"></script>
    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
