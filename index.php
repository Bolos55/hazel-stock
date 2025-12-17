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
                <input type="text" id="employeeName" placeholder="กรอกชื่อของคุณ" required>
                <button class="btn-primary" id="btnStartRecord">เริ่มบันทึก</button>
            </div>
        </section>

        <!-- Stock Recording Section -->
        <section class="stock-section hidden" id="stockSection">
            <div class="employee-info">
                <span>พนักงาน: <strong id="displayEmployeeName"></strong></span>
            </div>

            <!-- Progress Indicator -->
            <div class="progress-card">
                <div class="progress-stats">
                    <span>ความคืบหน้า: <strong id="progressText">0/0</strong></span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>

            <!-- Stock Items List -->
            <div class="stock-list" id="stockList">
                <!-- Items will be loaded dynamically -->
            </div>

            <!-- Submit Button -->
            <div class="submit-section">
                <button class="btn-submit" id="btnSubmit" disabled>
                    ส่งข้อมูลสต็อกวันนี้
                </button>
            </div>
        </section>

        <!-- Already Submitted Message -->
        <section class="submitted-section hidden" id="submittedSection">
            <div class="submitted-card">
                <div class="success-icon">✅</div>
                <h2>บันทึกสต็อกวันนี้แล้ว</h2>
                <p>ข้อมูลถูกบันทึกเรียบร้อย ไม่สามารถแก้ไขได้</p>
                <div class="submitted-info" id="submittedInfo"></div>
            </div>
        </section>

        <!-- Loading Overlay -->
        <div class="loading-overlay hidden" id="loadingOverlay">
            <div class="spinner"></div>
            <p>กำลังประมวลผล...</p>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="modal hidden" id="cameraModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="cameraTitle">ถ่ายรูปวัตถุดิบ</h3>
                <button class="btn-close" id="btnCloseCamera">✕</button>
            </div>
            <div class="camera-container">
                <video id="cameraVideo" autoplay playsinline></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
                <img id="capturedImage" class="hidden">
            </div>
            <div class="camera-controls">
                <button class="btn-camera" id="btnCapture">📷 ถ่ายรูป</button>
                <button class="btn-camera hidden" id="btnRetake">🔄 ถ่ายใหม่</button>
                <button class="btn-camera hidden" id="btnConfirm">✓ ใช้รูปนี้</button>
            </div>
        </div>
    </div>

    <footer class="app-footer">
        <div class="footer-profile">
            <img src="assets/phuriboss.jpg" alt="PHURIBOSS">
            <span>© <span id="year"></span> Created by <strong>PHURIBOSS</strong></span>
        </div>
    </footer>




    <script src="/js/app.js"></script>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
