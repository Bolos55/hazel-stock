# 📱 PWA Setup Guide - Hazel Stock Management

## ✅ สิ่งที่ทำเสร็จแล้ว

### 1. **Web App Manifest** (`manifest.json`)
- ✅ กำหนดชื่อแอป, สี, ไอคอน
- ✅ รองรับ shortcuts (ทางลัด)
- ✅ กำหนด display mode เป็น standalone

### 2. **Service Worker** (`sw.js`)
- ✅ Cache static assets
- ✅ Offline support
- ✅ Network-first strategy สำหรับ API
- ✅ Cache-first strategy สำหรับ assets
- ✅ Auto-update mechanism

### 3. **PWA Icons**
- ✅ สร้างไอคอนครบทุกขนาด (72x72 ถึง 512x512)
- ✅ Favicon (32x32)
- ✅ Apple touch icon

### 4. **Offline Page** (`offline.html`)
- ✅ หน้าแสดงเมื่อไม่มีอินเทอร์เน็ต
- ✅ Auto-detect เมื่อกลับมา online
- ✅ คำแนะนำการแก้ไข

### 5. **Install Prompt** (`pwa-install.js`)
- ✅ Custom install banner
- ✅ รองรับทั้ง Android และ iOS
- ✅ คำแนะนำติดตั้งสำหรับ iOS

### 6. **HTML Updates**
- ✅ เพิ่ม manifest link ในทุกหน้า
- ✅ เพิ่ม favicon และ apple-touch-icon
- ✅ Register service worker
- ✅ PWA install prompt

---

## 🚀 การทดสอบ PWA

### **1. ทดสอบบน Localhost**

```bash
# เริ่ม PHP server
php -S localhost:8000

# เปิดเบราว์เซอร์
http://localhost:8000
```

**ตรวจสอบ:**
- เปิด DevTools (F12)
- ไปที่ Application tab
- ดู Manifest, Service Workers, Storage

### **2. ทดสอบด้วย Lighthouse**

1. เปิด Chrome DevTools (F12)
2. ไปที่ tab "Lighthouse"
3. เลือก "Progressive Web App"
4. คลิก "Generate report"

**คะแนนที่ควรได้:**
- ✅ Installable
- ✅ PWA Optimized
- ✅ Works Offline
- ✅ Fast and Reliable

### **3. ทดสอบการติดตั้ง**

**บน Chrome (Desktop/Android):**
1. เปิดเว็บไซต์
2. รอ install banner ปรากฏ (3 วินาที)
3. คลิก "ติดตั้ง"
4. ตรวจสอบว่าแอปปรากฏบนหน้าจอหลัก

**บน iOS (Safari):**
1. เปิดเว็บไซต์ใน Safari
2. กดปุ่ม Share (📤)
3. เลื่อนหา "Add to Home Screen"
4. กด "Add"

### **4. ทดสอบ Offline Mode**

1. เปิดเว็บไซต์
2. เปิด DevTools → Application → Service Workers
3. เช็ค "Offline"
4. รีเฟรชหน้า → ควรเห็นหน้า offline.html
5. ลองเปิดหน้าที่ cache ไว้ → ควรทำงานได้

---

## 🌐 การ Deploy บน Production

### **ข้อกำหนด:**
1. ✅ **HTTPS** - บังคับใช้ (PWA ต้องการ HTTPS)
2. ✅ **Valid SSL Certificate**
3. ✅ **Correct MIME types** สำหรับ manifest.json

### **Deploy บน Render.com:**

Render.com มี HTTPS ให้ฟรีอยู่แล้ว ✅

1. Push code ไปที่ GitHub
2. Connect repository กับ Render
3. Deploy
4. ทดสอบ PWA บน production URL

### **ตรวจสอบ HTTPS:**

```bash
# ตรวจสอบว่า manifest.json โหลดได้
curl -I https://your-domain.com/manifest.json

# ควรได้ Content-Type: application/json
```

---

## 📊 PWA Features Checklist

### **Core Features:**
- ✅ Web App Manifest
- ✅ Service Worker
- ✅ HTTPS (บน production)
- ✅ Responsive Design
- ✅ Offline Support

### **Enhanced Features:**
- ✅ Install Prompt
- ✅ Offline Page
- ✅ Cache Strategy
- ✅ Update Notification
- ✅ App Shortcuts

### **Mobile Features:**
- ✅ Camera Access
- ✅ Touch Optimized
- ✅ Viewport Meta Tag
- ✅ Theme Color
- ✅ Apple Touch Icon

---

## 🔧 การอัพเดท PWA

### **เมื่อมีการเปลี่ยนแปลงโค้ด:**

1. **อัพเดท Cache Version** ใน `sw.js`:
```javascript
const CACHE_NAME = 'hazel-stock-v1.0.1'; // เปลี่ยนเวอร์ชัน
```

2. **Push code ขึ้น server**

3. **User จะได้รับการแจ้งเตือน:**
   - "🎉 มีเวอร์ชันใหม่! ต้องการอัพเดทหรือไม่?"
   - กด OK → รีเฟรชอัตโนมัติ

### **Force Update All Users:**

```javascript
// ใน sw.js
self.addEventListener('install', event => {
  self.skipWaiting(); // ข้าม waiting state
});

self.addEventListener('activate', event => {
  event.waitUntil(clients.claim()); // ควบคุมทันที
});
```

---

## 🐛 Troubleshooting

### **ปัญหา: Service Worker ไม่ register**

**แก้ไข:**
1. ตรวจสอบว่าใช้ HTTPS หรือ localhost
2. เช็ค Console สำหรับ error
3. ตรวจสอบ path ของ sw.js (ต้องอยู่ที่ root)

### **ปัญหา: Manifest ไม่โหลด**

**แก้ไข:**
1. ตรวจสอบ Content-Type: `application/json`
2. ตรวจสอบ JSON syntax
3. เช็ค path ของ icons

### **ปัญหา: ไม่แสดง Install Prompt**

**แก้ไข:**
1. ต้องใช้ HTTPS (หรือ localhost)
2. ต้องมี manifest.json ที่ valid
3. ต้องมี service worker ที่ active
4. ต้องมี icons ขนาด 192x192 และ 512x512
5. ต้องมี start_url ที่ถูกต้อง

### **ปัญหา: Offline ไม่ทำงาน**

**แก้ไข:**
1. ตรวจสอบว่า service worker active แล้ว
2. เช็ค cache strategy
3. ตรวจสอบว่า assets ถูก cache แล้ว
4. ลอง hard refresh (Ctrl+Shift+R)

---

## 📱 การใช้งาน PWA Features

### **1. Add to Home Screen**

```javascript
// ตรวจสอบว่าติดตั้งแล้วหรือยัง
if (window.matchMedia('(display-mode: standalone)').matches) {
  console.log('Running as PWA');
}
```

### **2. Offline Detection**

```javascript
// ตรวจสอบสถานะ online/offline
window.addEventListener('online', () => {
  console.log('Back online!');
});

window.addEventListener('offline', () => {
  console.log('Gone offline!');
});
```

### **3. Cache Management**

```javascript
// ลบ cache ทั้งหมด
caches.keys().then(names => {
  names.forEach(name => caches.delete(name));
});

// ลบ cache เฉพาะ
caches.delete('hazel-stock-v1');
```

---

## 🎯 Next Steps

### **Phase 1: Basic PWA** ✅ เสร็จแล้ว
- ✅ Manifest
- ✅ Service Worker
- ✅ Icons
- ✅ Offline Page

### **Phase 2: Enhanced Features** (Optional)
- ⏳ Push Notifications
- ⏳ Background Sync
- ⏳ Periodic Background Sync
- ⏳ Share Target API

### **Phase 3: Advanced** (Optional)
- ⏳ IndexedDB สำหรับ offline data
- ⏳ Web Share API
- ⏳ File System Access API
- ⏳ Badge API

---

## 📚 Resources

- [PWA Checklist](https://web.dev/pwa-checklist/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Web App Manifest](https://web.dev/add-manifest/)
- [Workbox](https://developers.google.com/web/tools/workbox) - Advanced SW library

---

## ✨ สรุป

โปรเจกต์ Hazel Stock Management พร้อมใช้งานเป็น PWA แล้ว! 🎉

**ขั้นตอนสุดท้าย:**
1. Deploy บน HTTPS
2. ทดสอบด้วย Lighthouse
3. ทดสอบติดตั้งบนมือถือ
4. แจ้งให้ user ติดตั้ง

**ประโยชน์ที่ได้:**
- ✅ ติดตั้งบนหน้าจอโทรศัพท์
- ✅ ทำงาน offline ได้
- ✅ เร็วขึ้น (caching)
- ✅ ประสบการณ์เหมือนแอปจริง
- ✅ ไม่ต้องผ่าน App Store

---

**Made with ❤️ by Kiro AI**
