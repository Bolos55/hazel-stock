<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>บันทึกสต็อกวัตถุดิบ</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="font-thai bg-hazel-bg min-h-screen flex flex-col">
    <div class="app-container">
        <!-- Header -->
        <header class="bg-white border-b border-gray-200">
            <div class="text-center py-5 px-4">
                <img src="assets/hazel-logo.png" alt="Hazel" class="max-w-44 w-full h-auto mx-auto mb-2">
                <div class="text-sm tracking-wider text-gray-800 mb-2">Beverages & Appetizers</div>
                <h1 class="text-xl tracking-wider text-hazel-red font-medium">บันทึกสต็อกวัตถุดิบ</h1>
                <div class="text-sm text-gray-600 mt-2" id="currentDate"></div>
            </div>
        </header>


        <!-- Employee Info Section -->
        <section class="py-8 px-5" id="employeeSection">
            <div class="card p-8">
                <label for="employeeName" class="block text-base font-semibold mb-3 text-gray-800">ชื่อพนักงาน</label>
                <input type="text" id="employeeName" placeholder="กรอกชื่อของคุณ" required class="input-field mb-5">
                <button class="btn-primary" id="btnStartRecord">เริ่มบันทึก</button>
            </div>
        </section>

        <!-- Stock Recording Section -->
        <section class="pb-24 hidden" id="stockSection">
            <div class="bg-white py-4 px-5 text-center text-base border-b-2 border-gray-100">
                <span>พนักงาน: <strong id="displayEmployeeName" class="text-gray-800"></strong></span>
            </div>

            <!-- Progress Indicator -->
            <div class="card p-5 mx-5 my-5">
                <div class="text-center mb-3 text-base">
                    <span>ความคืบหน้า: <strong id="progressText" class="text-gray-800">0/0</strong></span>
                </div>
                <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                    <div class="h-full bg-hazel-red rounded-full transition-all duration-300 ease-out w-0" id="progressFill"></div>
                </div>
            </div>

            <!-- Stock Items List -->
            <div class="px-5" id="stockList">
                <!-- Items will be loaded dynamically -->
            </div>

            <!-- Submit Button -->
            <div class="px-5 mt-6">
                <button class="btn-submit" id="btnSubmit" disabled>
                    ส่งข้อมูลสต็อกวันนี้
                </button>
            </div>
        </section>

        <!-- Already Submitted Message -->
        <section class="py-10 px-5 text-center hidden" id="submittedSection">
            <div class="card p-10">
                <div class="text-6xl mb-5">✅</div>
                <h2 class="text-2xl text-green-600 mb-3 font-semibold">บันทึกสต็อกวันนี้แล้ว</h2>
                <p class="text-base text-gray-600 mb-5">ข้อมูลถูกบันทึกเรียบร้อย ไม่สามารถแก้ไขได้</p>
                <div class="bg-gray-100 p-4 rounded-xl text-sm text-gray-600" id="submittedInfo"></div>
            </div>
        </section>

        <!-- Loading Overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-80 flex flex-col items-center justify-center z-50 text-white hidden" id="loadingOverlay">
            <div class="w-12 h-12 border-4 border-white border-opacity-30 border-t-white rounded-full animate-spin mb-5"></div>
            <p class="text-lg">กำลังประมวลผล...</p>
        </div>
    </div>

    <!-- Camera Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-95 z-50 flex items-center justify-center hidden" id="cameraModal">
        <div class="w-full max-w-2xl h-screen bg-black flex flex-col">
            <div class="bg-hazel-red p-5 flex justify-between items-center text-white">
                <h3 id="cameraTitle" class="text-lg font-medium">ถ่ายรูปวัตถุดิบ</h3>
                <button class="bg-transparent border-none text-white text-2xl cursor-pointer p-0 w-10 h-10" id="btnCloseCamera">✕</button>
            </div>
            <div class="flex-1 relative flex items-center justify-center bg-black">
                <video id="cameraVideo" autoplay playsinline class="w-full h-full object-contain"></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
                <img id="capturedImage" class="w-full h-full object-contain hidden">
            </div>
            <div class="bg-gray-900 p-5 flex gap-3 justify-center">
                <button class="flex-1 py-4 px-6 border-none rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 bg-orange-500 text-white" id="btnCapture">📷 ถ่ายรูป</button>
                <button class="flex-1 py-4 px-6 border-none rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 bg-red-500 text-white hidden" id="btnRetake">🔄 ถ่ายใหม่</button>
                <button class="flex-1 py-4 px-6 border-none rounded-xl text-base font-semibold cursor-pointer transition-all duration-300 bg-green-500 text-white hidden" id="btnConfirm">✓ ใช้รูปนี้</button>
            </div>
        </div>
    </div>

    <footer class="bg-gray-50 border-t border-gray-200 py-3 px-2 text-center mt-auto">
        <div class="flex items-center justify-center gap-2 text-xs text-gray-500">
            <img src="assets/phuriboss.jpg" alt="PHURIBOSS" class="w-7 h-7 rounded-full object-cover border border-gray-300">
            <span>© <span id="year"></span> Created by <strong class="text-gray-700 tracking-wide">PHURIBOSS</strong></span>
        </div>
    </footer>




    <script src="/js/app.js"></script>

    <script>
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
