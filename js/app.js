// =====================================
// Stock Recording Application (READY)
// =====================================
class StockApp {
    constructor() {
        this.materials = [];
        this.stockData = {};
        this.employeeName = '';
        this.currentMaterial = null;
        this.cameraStream = null;

        this.init();
    }

    /* ================= INIT ================= */
    init() {
        this.setupEventListeners();
        this.updateCurrentDate();
        this.checkTodayRecord();
    }

    setupEventListeners() {
        document.getElementById('btnStartRecord')
            .addEventListener('click', () => this.startRecording());

        document.getElementById('btnSubmit')
            .addEventListener('click', () => this.submitStock());

        document.getElementById('btnCloseCamera')
            .addEventListener('click', () => this.closeCamera());

        document.getElementById('btnCapture')
            .addEventListener('click', () => this.capturePhoto());

        document.getElementById('btnRetake')
            .addEventListener('click', () => this.retakePhoto());

        document.getElementById('btnConfirm')
            .addEventListener('click', () => this.confirmPhoto());

        document.getElementById('employeeName')
            .addEventListener('keypress', e => {
                if (e.key === 'Enter') this.startRecording();
            });
    }

    /* ================= DATE ================= */
    updateCurrentDate() {
        const days = ['อาทิตย์','จันทร์','อังคาร','พุธ','พฤหัสบดี','ศุกร์','เสาร์'];
        const months = [
            'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
            'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'
        ];

        const d = new Date();
        document.getElementById('currentDate').textContent =
            `วัน${days[d.getDay()]}ที่ ${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()+543}`;
    }

    /* ================= CHECK TODAY ================= */
    async checkTodayRecord() {
        try {
            const res = await fetch('api/get-today-record.php');
            const data = await res.json();
            if (data.success && data.hasRecord) {
                this.showSubmittedSection(data.record);
            }
        } catch (e) {
            console.warn(e);
        }
    }

    /* ================= START ================= */
    async startRecording() {
        const name = document.getElementById('employeeName').value.trim();
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
        this.showLoading(true);
        try {
            const res = await fetch('api/get-materials.php');
            const data = await res.json();

            if (!data.success) throw new Error();

            this.materials = data.materials;
            this.initializeStockData();
            this.renderStockList();
            this.updateProgress();

        } catch {
            alert('โหลดข้อมูลวัตถุดิบไม่สำเร็จ');
        } finally {
            this.showLoading(false);
        }
    }

    initializeStockData() {
        this.materials.forEach(m => {
            this.stockData[m.id] = {
                material_id: m.id,
                material_name: m.material_name,
                unit: m.unit,
                quantity: 0,
                photo: null,
                photoData: null
            };
        });
    }

    /* ================= UI ================= */
    renderStockList() {
        const box = document.getElementById('stockList');
        box.innerHTML = '';
        this.materials.forEach(m => box.appendChild(this.createStockItem(m)));
    }

    createStockItem(material) {
        const step = this.getStepByUnit(material.unit);
        const data = this.stockData[material.id];

        const div = document.createElement('div');
        div.className = 'stock-item';
        div.dataset.materialId = material.id;

        div.innerHTML = `
            <div class="stock-item-header">
                <div class="material-name">${material.material_name}</div>
                <div class="photo-status">${data.photo ? '✅' : '❌'}</div>
            </div>

            <div class="stock-item-controls">
                <div class="quantity-controls">
                    <button class="btn-quantity" data-action="dec">−</button>

                    <div class="quantity-display">
                        <input type="number" class="quantity-input"
                               value="${data.quantity}" min="0" step="${step}">
                        <div class="quantity-unit">${material.unit}</div>
                    </div>

                    <button class="btn-quantity" data-action="inc">+</button>
                </div>

                <button class="btn-take-photo ${data.photo ? 'has-photo' : ''}">
                    📷 ${data.photo ? 'แก้ไขรูป' : 'ถ่ายรูป'}
                </button>
            </div>
        `;

        const input = div.querySelector('.quantity-input');
        const dec = div.querySelector('[data-action="dec"]');
        const inc = div.querySelector('[data-action="inc"]');
        const photoBtn = div.querySelector('.btn-take-photo');

        dec.onclick = () => this.setQty(material.id, input, -step);
        inc.onclick = () => this.setQty(material.id, input, step);

        input.oninput = () => {
            let v = Math.max(0, Number(input.value) || 0);
            v = Math.round(v / step) * step;
            input.value = v;
            this.updateQuantityFromInput(material.id, v);
        };

        photoBtn.onclick = () => this.openCamera(material.id);

        return div;
    }

    setQty(id, input, diff) {
        let v = Number(input.value) || 0;
        v = Math.max(0, v + diff);
        input.value = v;
        this.updateQuantityFromInput(id, v);
    }

    getStepByUnit(unit) {
        if (['ถุง','ขวด','ชิ้น'].includes(unit)) return 1;
        if (['กรัม','มล.'].includes(unit)) return 10;
        return 1;
    }

    /* ================= PROGRESS ================= */
    updateProgress() {
        const total = this.materials.length;
        let done = 0;

        this.materials.forEach(m => {
            if (this.stockData[m.id].photo !== null) done++;
        });

        document.getElementById('progressText').textContent = `${done}/${total}`;
        document.getElementById('progressFill').style.width = `${(done/total)*100}%`;
        document.getElementById('btnSubmit').disabled = done !== total;
    }

    updateQuantityFromInput(id, value) {
        this.stockData[id].quantity = value;
        this.updateProgress();
    }

    /* ================= CAMERA ================= */
    async openCamera(materialId) {
        this.currentMaterial = materialId;
        document.getElementById('cameraModal').classList.remove('hidden');

        try {
            this.cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            document.getElementById('cameraVideo').srcObject = this.cameraStream;
        } catch {
            alert('ไม่สามารถเปิดกล้องได้');
            this.closeCamera();
        }
    }

    capturePhoto() {
        const video = cameraVideo;
        const canvas = cameraCanvas;
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        capturedImage.src = canvas.toDataURL('image/jpeg', 0.8);
        video.classList.add('hidden');
        capturedImage.classList.remove('hidden');
        btnCapture.classList.add('hidden');
        btnRetake.classList.remove('hidden');
        btnConfirm.classList.remove('hidden');
    }

    retakePhoto() {
        cameraVideo.classList.remove('hidden');
        capturedImage.classList.add('hidden');
        btnCapture.classList.remove('hidden');
        btnRetake.classList.add('hidden');
        btnConfirm.classList.add('hidden');
    }

    async confirmPhoto() {
        const blob = await fetch(capturedImage.src).then(r => r.blob());
        const fd = new FormData();
        fd.append('photo', blob);
        fd.append('material_id', this.currentMaterial);
        fd.append('material_name', this.stockData[this.currentMaterial].material_name);

        const res = await fetch('api/upload-photo.php', { method:'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            this.stockData[this.currentMaterial].photo = data.photo_path;
            this.updateProgress();
            this.closeCamera();
        } else {
            alert(data.message);
        }
    }

    closeCamera() {
        if (this.cameraStream) {
            this.cameraStream.getTracks().forEach(t => t.stop());
            this.cameraStream = null;
        }
        cameraModal.classList.add('hidden');
    }

    /* ================= SUBMIT ================= */
    async submitStock() {
        if (!confirm('ยืนยันส่งข้อมูลสต็อกวันนี้?')) return;

        if (Object.values(this.stockData).some(d => d.photo === null)) {
            alert('กรุณาถ่ายรูปให้ครบทุกวัตถุดิบ');
            return;
        }

        const res = await fetch('api/submit-stock.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                employee_name: this.employeeName,
                stock_data: Object.values(this.stockData)
            })
        });

        const data = await res.json();
        if (data.success) location.reload();
        else alert(data.message);
    }

    showSubmittedSection(record) {
        employeeSection.classList.add('hidden');
        stockSection.classList.add('hidden');
        submittedSection.classList.remove('hidden');
    }

    showLoading(show) {
        loadingOverlay.classList.toggle('hidden', !show);
    }
}

document.addEventListener('DOMContentLoaded', () => new StockApp());
