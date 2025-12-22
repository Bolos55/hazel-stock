class StockApp {
    constructor() {
        this.materials = [];
        this.stockData = {};
        this.employeeName = '';
        this.photoData = {};

        document.addEventListener('DOMContentLoaded', () => {
            this.init();
        });
    }

    async init() {
        this.updateCurrentDate();
        await this.checkTodayRecord();
        this.bindEvents();
    }

    bindEvents() {
        document.getElementById('btnStartRecord')
            .addEventListener('click', () => this.startRecording());

        document.getElementById('btnSubmit')
            .addEventListener('click', () => this.submitStock());

        // Camera modal events
        document.getElementById('btnCloseCamera')
            .addEventListener('click', () => this.closeCameraModal());
        
        document.getElementById('btnCapture')
            .addEventListener('click', () => this.capturePhoto());
            
        document.getElementById('btnRetake')
            .addEventListener('click', () => this.retakePhoto());
            
        document.getElementById('btnConfirm')
            .addEventListener('click', () => this.confirmPhoto());
    }

    updateCurrentDate() {
        const d = new Date();
        document.getElementById('currentDate').textContent =
            d.toLocaleDateString('th-TH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
    }

    /* ================= CHECK TODAY ================= */
    async checkTodayRecord() {
        try {
            const res = await fetch('/api/get-today-record.php');
            
            // Check if response is ok
            if (!res.ok) {
                console.error('HTTP Error:', res.status, res.statusText);
                return;
            }
            
            // Check if response is JSON
            const contentType = res.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('Response is not JSON:', contentType);
                const text = await res.text();
                console.error('Response text:', text);
                return;
            }
            
            const data = await res.json();
            console.log('checkTodayRecord response:', data);

            if (data.success && data.has_records) {
                this.showSubmittedSection(data);
                return;
            }
        } catch (e) {
            console.error('checkTodayRecord error:', e);
            // Show user-friendly error
            this.showError('ไม่สามารถตรวจสอบข้อมูลวันนี้ได้ กรุณาลองใหม่');
        }
    }

    showSubmittedSection(data) {
        document.getElementById('employeeSection').classList.add('hidden');
        document.getElementById('submittedSection').classList.remove('hidden');
        
        const info = document.getElementById('submittedInfo');
        info.innerHTML = `
            <div>วันที่: ${data.date}</div>
            <div>จำนวนรายการ: ${data.total_records} รายการ</div>
            <div>สถานะ: บันทึกแล้ว</div>
        `;
    }

    showError(message) {
        // Create or update error message
        let errorDiv = document.getElementById('errorMessage');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.id = 'errorMessage';
            errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            document.querySelector('.app-container').insertBefore(errorDiv, document.querySelector('.app-container').firstChild);
        }
        errorDiv.textContent = message;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            if (errorDiv) errorDiv.remove();
        }, 5000);
    }

    /* ================= START ================= */
    async startRecording() {
        const input = document.getElementById('employeeName');
        const name = input.value.trim();

        if (!name) {
            alert('กรุณากรอกชื่อพนักงาน');
            return;
        }

        this.employeeName = name;
        document.getElementById('displayEmployeeName').textContent = name;

        document.getElementById('employeeSection').classList.add('hidden');
        document.getElementById('stockSection').classList.remove('hidden');

        await this.loadMaterials();
    }

    /* ================= MATERIALS ================= */
    async loadMaterials() {
        try {
            const res = await fetch('/api/get-materials.php');
            const data = await res.json();

            if (!data.success) throw new Error();

            this.materials = data.materials;
            this.initStockData();
            this.renderList();
            this.updateProgress();
        } catch (e) {
            alert('โหลดวัตถุดิบไม่สำเร็จ');
        }
    }

    initStockData() {
        this.materials.forEach(m => {
            this.stockData[m.id] = {
                material_id: m.id,
                quantity: 0
            };
            this.photoData[m.id] = null;
        });
    }

    renderList() {
        const box = document.getElementById('stockList');
        box.innerHTML = '';

        this.materials.forEach(m => {
            const div = document.createElement('div');
            div.className = 'card p-5 mb-4';
            div.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <div class="text-lg font-semibold text-gray-800">${m.material_name}</div>
                    <div class="text-2xl">${this.photoData[m.id] ? '📷' : '📷'}</div>
                </div>
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-4">
                        <button class="btn-quantity" data-action="decrease" data-id="${m.id}">-</button>
                        <div class="min-w-24 text-center">
                            <div class="text-2xl font-bold text-gray-800">${this.stockData[m.id].quantity}</div>
                            <div class="text-sm text-gray-600 mt-1">${m.unit}</div>
                        </div>
                        <button class="btn-quantity" data-action="increase" data-id="${m.id}">+</button>
                    </div>
                    <button class="btn-take-photo ${this.photoData[m.id] ? 'has-photo' : ''}" data-id="${m.id}">
                        📷 ถ่ายรูป
                    </button>
                </div>
            `;

            // Bind quantity controls
            div.querySelector('[data-action="decrease"]')
                .addEventListener('click', () => this.changeQuantity(m.id, -1));
            div.querySelector('[data-action="increase"]')
                .addEventListener('click', () => this.changeQuantity(m.id, 1));
            
            // Bind photo button
            div.querySelector('.btn-take-photo')
                .addEventListener('click', () => this.openCameraModal(m.id, m.material_name));

            box.appendChild(div);
        });
    }

    changeQuantity(materialId, delta) {
        const current = this.stockData[materialId].quantity;
        const newValue = Math.max(0, current + delta);
        this.stockData[materialId].quantity = newValue;
        
        this.renderList();
        this.updateProgress();
        this.checkSubmitButton();
    }

    updateProgress() {
        const total = this.materials.length;
        const completed = Object.values(this.stockData).filter(item => item.quantity > 0).length;
        const percentage = total > 0 ? (completed / total) * 100 : 0;

        document.getElementById('progressText').textContent = `${completed}/${total}`;
        document.getElementById('progressFill').style.width = `${percentage}%`;
    }

    checkSubmitButton() {
        const hasData = Object.values(this.stockData).some(item => item.quantity > 0);
        const submitBtn = document.getElementById('btnSubmit');
        submitBtn.disabled = !hasData;
    }

    /* ================= CAMERA ================= */
    currentMaterialId = null;
    currentMaterialName = '';
    stream = null;

    async openCameraModal(materialId, materialName) {
        this.currentMaterialId = materialId;
        this.currentMaterialName = materialName;
        
        document.getElementById('cameraTitle').textContent = `ถ่ายรูป: ${materialName}`;
        document.getElementById('cameraModal').classList.remove('hidden');

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            document.getElementById('cameraVideo').srcObject = this.stream;
        } catch (e) {
            alert('ไม่สามารถเปิดกล้องได้');
            this.closeCameraModal();
        }
    }

    closeCameraModal() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        document.getElementById('cameraModal').classList.add('hidden');
        document.getElementById('cameraVideo').classList.remove('hidden');
        document.getElementById('capturedImage').classList.add('hidden');
        document.getElementById('btnCapture').classList.remove('hidden');
        document.getElementById('btnRetake').classList.add('hidden');
        document.getElementById('btnConfirm').classList.add('hidden');
    }

    capturePhoto() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const img = document.getElementById('capturedImage');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        img.src = dataURL;
        
        video.classList.add('hidden');
        img.classList.remove('hidden');
        document.getElementById('btnCapture').classList.add('hidden');
        document.getElementById('btnRetake').classList.remove('hidden');
        document.getElementById('btnConfirm').classList.remove('hidden');
    }

    retakePhoto() {
        document.getElementById('cameraVideo').classList.remove('hidden');
        document.getElementById('capturedImage').classList.add('hidden');
        document.getElementById('btnCapture').classList.remove('hidden');
        document.getElementById('btnRetake').classList.add('hidden');
        document.getElementById('btnConfirm').classList.add('hidden');
    }

    async confirmPhoto() {
        const canvas = document.getElementById('cameraCanvas');
        const dataURL = canvas.toDataURL('image/jpeg', 0.8);
        
        this.photoData[this.currentMaterialId] = dataURL;
        this.closeCameraModal();
        this.renderList();
    }

    /* ================= SUBMIT ================= */
    async submitStock() {
        if (!confirm('ยืนยันส่งข้อมูล?')) return;

        this.showLoading(true);

        const payload = {
            employee_name: this.employeeName,
            stock_data: Object.values(this.stockData),
            photos: this.photoData
        };

        try {
            const res = await fetch('/api/submit-stock.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.success) {
                alert('บันทึกสำเร็จ');
                location.reload();
            } else {
                alert(data.message || 'ผิดพลาด');
            }
        } catch (e) {
            alert('ส่งข้อมูลไม่สำเร็จ');
        } finally {
            this.showLoading(false);
        }
    }

    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    }
}

new StockApp();
