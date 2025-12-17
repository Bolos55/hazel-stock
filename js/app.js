class StockApp {
    constructor() {
        this.materials = [];
        this.stockData = {};
        this.employeeName = '';

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
            const data = await res.json();

            if (data.success && data.exists) {
                alert('วันนี้มีการบันทึกข้อมูลแล้ว');
            }
        } catch (e) {
            console.warn('checkTodayRecord error', e);
        }
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
        });
    }

    renderList() {
        const box = document.getElementById('stockList');
        box.innerHTML = '';

        this.materials.forEach(m => {
            const div = document.createElement('div');
            div.innerHTML = `
                <label>${m.material_name} (${m.unit})</label>
                <input type="number" min="0" value="0"
                    data-id="${m.id}">
            `;
            div.querySelector('input')
                .addEventListener('input', e => {
                    this.stockData[m.id].quantity = Number(e.target.value);
                });

            box.appendChild(div);
        });
    }

    /* ================= SUBMIT ================= */
    async submitStock() {
        if (!confirm('ยืนยันส่งข้อมูล?')) return;

        const payload = {
            employee_name: this.employeeName,
            stock_data: Object.values(this.stockData)
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
        }
    }
}

new StockApp();
