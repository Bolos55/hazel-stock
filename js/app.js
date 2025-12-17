// Stock Recording Application
class StockApp {
    constructor() {
        this.materials = [];
        this.stockData = {};
        this.employeeName = '';
        this.currentMaterial = null;
        this.cameraStream = null;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.updateCurrentDate();
        this.checkTodayRecord();
    }
    
    setupEventListeners() {
        document.getElementById('btnStartRecord').addEventListener('click', () => this.startRecording());
        document.getElementById('btnSubmit').addEventListener('click', () => this.submitStock());
        document.getElementById('btnCloseCamera').addEventListener('click', () => this.closeCamera());
        document.getElementById('btnCapture').addEventListener('click', () => this.capturePhoto());
        document.getElementById('btnRetake').addEventListener('click', () => this.retakePhoto());
        document.getElementById('btnConfirm').addEventListener('click', () => this.confirmPhoto());
        
        // Enter key on employee name input
        document.getElementById('employeeName').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.startRecording();
            }
        });
    }
    
    updateCurrentDate() {
        const thai_days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
        const thai_months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 
                             'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        
        const now = new Date();
        const dayName = thai_days[now.getDay()];
        const day = now.getDate();
        const month = thai_months[now.getMonth()];
        const year = now.getFullYear() + 543; // Buddhist year
        
        document.getElementById('currentDate').textContent = 
            `วัน${dayName}ที่ ${day} ${month} ${year}`;
    }
    
    async checkTodayRecord() {
        try {
            const response = await fetch('api/get-today-record.php')
            const data = await response.json();
            
            if (data.success && data.hasRecord) {
                this.showSubmittedSection(data.record);
            }
        } catch (error) {
            console.error('Error checking today record:', error);
        }
    }
    
    async startRecording() {
        const nameInput = document.getElementById('employeeName');
        const name = nameInput.value.trim();
        
        if (!name) {
            alert('กรุณากรอกชื่อพนักงาน');
            nameInput.focus();
            return;
        }
        
        this.employeeName = name;
        document.getElementById('displayEmployeeName').textContent = name;
        
        // Hide employee section, show stock section
        document.getElementById('employeeSection').classList.add('hidden');
        document.getElementById('stockSection').classList.remove('hidden');
        
        // Load materials
        await this.loadMaterials();
    }
    
    async loadMaterials() {
        this.showLoading(true);
        
        try {
            const response = await fetch('api/get-materials.php')
            const data = await response.json();
            
            if (data.success) {
                this.materials = data.materials;
                this.initializeStockData();
                this.renderStockList();
                this.updateProgress();
            } else {
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            }
        } catch (error) {
            console.error('Error loading materials:', error);
            alert('ไม่สามารถเชื่อมต่อกับระบบได้');
        } finally {
            this.showLoading(false);
        }
    }
    
    initializeStockData() {
        this.materials.forEach(material => {
            this.stockData[material.id] = {
                material_id: material.id,
                material_name: material.material_name,
                unit: material.unit,
                quantity: 0,
                photo: null,
                photoData: null
            };
        });
    }
    
    renderStockList() {
        const container = document.getElementById('stockList');
        container.innerHTML = '';
        
        this.materials.forEach(material => {
            const item = this.createStockItemElement(material);
            container.appendChild(item);
        });
    }
    
    createStockItemElement(material) {
            const div = document.createElement('div');
            div.className = 'stock-item';
            div.setAttribute('data-material-id', material.id);

            const hasPhoto = this.stockData[material.id].photo !== null;
            const step = this.getStepByUnit(material.unit);

            div.innerHTML = `
                <div class="stock-item-header">
                    <div class="material-name">${material.material_name}</div>
                    <div class="photo-status">${hasPhoto ? '✅' : '❌'}</div>
                </div>

                <div class="stock-item-controls">
                    <div class="quantity-controls">
                        <button class="btn-quantity" data-action="decrease">−</button>

                        <div class="quantity-display">
                            <input 
                                type="number"
                                class="quantity-input"
                                min="0"
                                step="${step}"
                                value="${this.stockData[material.id].quantity}"
                            >
                            <div class="quantity-unit">${material.unit}</div>
                        </div>

                        <button class="btn-quantity" data-action="increase">+</button>
                    </div>

                    <button class="btn-take-photo ${hasPhoto ? 'has-photo' : ''}">
                        📷 ${hasPhoto ? 'แก้ไขรูป' : 'ถ่ายรูป'}
                    </button>
                </div>
            `;

            const decreaseBtn = div.querySelector('[data-action="decrease"]');
            const increaseBtn = div.querySelector('[data-action="increase"]');
            const input = div.querySelector('.quantity-input');
            const photoBtn = div.querySelector('.btn-take-photo');

            decreaseBtn.addEventListener('click', () => {
                input.value = Math.max(0, Number(input.value) - step);
                this.updateQuantityFromInput(material.id, Number(input.value));
            });

            increaseBtn.addEventListener('click', () => {
                input.value = Number(input.value) + step;
                this.updateQuantityFromInput(material.id, Number(input.value));
            });

            input.addEventListener('input', () => {
                const value = Math.max(0, Number(input.value) || 0);
                input.value = value;
                this.updateQuantityFromInput(material.id, value);
            });

            photoBtn.addEventListener('click', () => this.openCamera(material.id));

            return div;
        }

    getStepByUnit(unit) {
            switch (unit) {
                case 'ถุง':
                case 'ขวด':
                case 'ชิ้น':
                    return 1;

                case 'กรัม':
                    return 10;

                case 'มล.':
                    return 10;

                default:
                    return 1;
            }
        }
    
    updateProgress() {
        const total = this.materials.length;
        let completed = 0;
        
        this.materials.forEach(material => {
            const data = this.stockData[material.id];
            if (data.quantity > 0 && data.photo !== null) {
                completed++;
            }
        });
        
        const percentage = (completed / total) * 100;
        
        document.getElementById('progressText').textContent = `${completed}/${total}`;
        document.getElementById('progressFill').style.width = `${percentage}%`;
        
        // Enable/disable submit button
        const submitBtn = document.getElementById('btnSubmit');
        submitBtn.disabled = completed !== total;
    }
    
    async openCamera(materialId) {
        this.currentMaterial = materialId;
        const material = this.materials.find(m => m.id === materialId);
        
        document.getElementById('cameraTitle').textContent = `ถ่ายรูป: ${material.material_name}`;
        document.getElementById('cameraModal').classList.remove('hidden');
        
        // Reset camera UI
        document.getElementById('btnCapture').classList.remove('hidden');
        document.getElementById('btnRetake').classList.add('hidden');
        document.getElementById('btnConfirm').classList.add('hidden');
        document.getElementById('capturedImage').classList.add('hidden');
        document.getElementById('cameraVideo').classList.remove('hidden');
        
        try {
            this.cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: 1920, height: 1080 }
            });
            document.getElementById('cameraVideo').srcObject = this.cameraStream;
        } catch (error) {
            console.error('Camera error:', error);
            alert('ไม่สามารถเปิดกล้องได้ กรุณาอนุญาตการใช้งานกล้อง');
            this.closeCamera();
        }
    }
    
    capturePhoto() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const image = document.getElementById('capturedImage');
        
        // Set canvas size to video size
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        // Convert to image
        const imageData = canvas.toDataURL('image/jpeg', 0.8);
        image.src = imageData;
        
        // Show captured image, hide video
        video.classList.add('hidden');
        image.classList.remove('hidden');
        
        // Update buttons
        document.getElementById('btnCapture').classList.add('hidden');
        document.getElementById('btnRetake').classList.remove('hidden');
        document.getElementById('btnConfirm').classList.remove('hidden');
    }
    
    retakePhoto() {
        const video = document.getElementById('cameraVideo');
        const image = document.getElementById('capturedImage');
        
        video.classList.remove('hidden');
        image.classList.add('hidden');
        
        document.getElementById('btnCapture').classList.remove('hidden');
        document.getElementById('btnRetake').classList.add('hidden');
        document.getElementById('btnConfirm').classList.add('hidden');
    }
    
    async confirmPhoto() {
        this.showLoading(true);
        
        const image = document.getElementById('capturedImage');

            if (!image.src || image.classList.contains('hidden')) {
                    alert('กรุณาถ่ายรูปก่อน');
                    this.showLoading(false);
                    return;
             }

        const blob = await fetch(image.src).then(r => r.blob());
        
        // Upload photo
        const formData = new FormData();
        formData.append('photo', blob, `${this.currentMaterial}.jpg`);
        formData.append('material_id', this.currentMaterial);
        formData.append('material_name', this.stockData[this.currentMaterial].material_name);

        console.log('Uploading photo:', {
            material_id: this.currentMaterial,
            material_name: this.stockData[this.currentMaterial].material_name,
            blob_size: blob.size
        });
        
        try {
            const response = await fetch('api/upload-photo.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                    throw new Error('Server error: ' + response.status);
                }
    
            const data = await response.json();
            
            if (data.success) {
                this.stockData[this.currentMaterial].photo = data.photo_path;
                this.stockData[this.currentMaterial].photoData = image.src;
                
                // Update UI
                const item = document.querySelector(`[data-material-id="${this.currentMaterial}"]`);
                item.querySelector('.photo-status').textContent = '✅';
                const photoBtn = item.querySelector('.btn-take-photo');
                photoBtn.textContent = '📷 แก้ไขรูป';
                photoBtn.classList.add('has-photo');
                
                this.updateProgress();
                this.closeCamera();
            } else {
                alert('ไม่สามารถบันทึกรูปได้: ' + data.message);
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('เกิดข้อผิดพลาดในการบันทึกรูป');
        } finally {
            this.showLoading(false);
        }
    }
    
    closeCamera() {
        if (this.cameraStream) {
            this.cameraStream.getTracks().forEach(track => track.stop());
            this.cameraStream = null;
        }
        document.getElementById('cameraModal').classList.add('hidden');
    }
    
    async submitStock() {
        if (!confirm('ยืนยันการส่งข้อมูลสต็อกวันนี้?\nข้อมูลจะไม่สามารถแก้ไขได้หลังจากส่งแล้ว')) {
            return;
        }
        
        this.showLoading(true);
        
        const submitData = {
            employee_name: this.employeeName,
            stock_data: Object.values(this.stockData)
        };
        
        try {
            const response = await fetch('api/submit-stock.php', {

                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(submitData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('✅ บันทึกข้อมูลสำเร็จ!');
                location.reload();
            } else {
                alert('เกิดข้อผิดพลาด: ' + data.message);
            }
        } catch (error) {
            console.error('Submit error:', error);
            alert('ไม่สามารถส่งข้อมูลได้ กรุณาลองใหม่');
        } finally {
            this.showLoading(false);
        }
    }
    
    showSubmittedSection(record) {
        document.getElementById('employeeSection').classList.add('hidden');
        document.getElementById('stockSection').classList.add('hidden');
        document.getElementById('submittedSection').classList.remove('hidden');
        
        const submittedTime = new Date(record.submitted_at);
        const timeStr = submittedTime.toLocaleTimeString('th-TH');
        
        document.getElementById('submittedInfo').innerHTML = `
            <p>บันทึกโดย: <strong>${record.employee_name}</strong></p>
            <p>เวลา: ${timeStr}</p>
        `;
    }
    
    showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (show) {
            overlay.classList.remove('hidden');
        } else {
            overlay.classList.add('hidden');
        }
    }

    updateQuantityFromInput(materialId, value) {
    this.stockData[materialId].quantity = value;
    this.updateProgress();
}

}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new StockApp();
});