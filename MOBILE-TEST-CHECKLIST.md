# 📱 Mobile Testing Checklist

## ✅ Features to Test on Mobile Phone

### 1. Camera Functionality
- [ ] Open http://localhost:8000 on your phone (use your computer's IP address if testing on phone)
- [ ] Click "เริ่มบันทึก" button
- [ ] Click "📷 ถ่ายรูป" button
- [ ] **Expected**: Phone should open rear camera (not front camera)
- [ ] Take a photo
- [ ] **Expected**: Photo preview should appear with "✅ รูปพร้อมแล้ว"

### 2. Gallery Selection (Fallback)
- [ ] Click "📁 เลือกจากแกลเลอรี่" button
- [ ] **Expected**: Phone should open photo gallery
- [ ] Select an existing photo
- [ ] **Expected**: Photo preview should appear

### 3. Photo Persistence After Refresh
- [ ] Take/select photos for 2-3 materials
- [ ] Fill in quantity values
- [ ] **Refresh the page** (pull down or press refresh)
- [ ] Click "เริ่มบันทึก" again with same employee name
- [ ] **Expected**: All photos should reappear automatically
- [ ] **Expected**: All quantity values should be restored

### 4. Quantity Persistence
- [ ] Fill in "จำนวนหน่วยหลัก" (main unit) - e.g., 2
- [ ] Fill in "จำนวนหน่วยย่อย" (sub unit) - e.g., 1.5
- [ ] Refresh the page
- [ ] Click "เริ่มบันทึก" again
- [ ] **Expected**: Both quantity fields should show saved values

### 5. Photo Retake
- [ ] Click "🔄 ถ่ายใหม่" button on any photo
- [ ] **Expected**: Photo preview disappears
- [ ] **Expected**: "✅ รูปพร้อมแล้ว" status disappears
- [ ] Take a new photo
- [ ] **Expected**: New photo appears

### 6. Submit Stock
- [ ] Fill in quantities for at least 1 material
- [ ] Take photos for all materials with quantities
- [ ] Click "บันทึกข้อมูลสต็อก"
- [ ] **Expected**: Success message appears
- [ ] **Expected**: Shows correct count of items and photos

### 7. Start New (Clear Data)
- [ ] After successful submission, click "🔄 บันทึกใหม่"
- [ ] **Expected**: All photos cleared
- [ ] **Expected**: All quantities cleared
- [ ] **Expected**: localStorage cleared

## 🔧 Troubleshooting

### If Camera Doesn't Open:
1. Check browser permissions (Settings > Safari/Chrome > Camera)
2. Make sure you're using HTTPS or localhost
3. Try using "📁 เลือกจากแกลเลอรี่" as fallback

### If Photos Don't Persist:
1. Check browser localStorage is enabled
2. Check if you're using same employee name
3. Check browser console for errors (F12 or inspect)

### If Photos Are Too Large:
- Photos are automatically resized to 600x400px
- Quality reduced to 60% for faster loading
- Max file size: 2MB

## 📊 Expected Behavior

### Camera Input Attributes:
```html
<input type="file" 
       accept="image/*" 
       capture="environment"  <!-- This forces rear camera -->
       ...>
```

### Storage Keys:
- Photos: `photoStorage_YYYY-MM-DD_EmployeeName`
- Quantities: `quantityStorage_YYYY-MM-DD_EmployeeName`
- Auto-cleanup: Data older than 7 days is removed

## 🌐 Access from Phone

### Option 1: Same Network
1. Find your computer's IP address:
   - Windows: `ipconfig` (look for IPv4)
   - Example: 192.168.1.100
2. On phone browser: `http://192.168.1.100:8000`

### Option 2: USB Tethering
1. Connect phone to computer via USB
2. Enable USB tethering on phone
3. Access via tethered IP address

### Option 3: Deploy to Server
- Upload to web hosting with HTTPS
- Camera works best with HTTPS

## ✨ Key Features Implemented

1. ✅ Dual quantity input (main unit + sub unit)
2. ✅ Camera capture with rear camera preference
3. ✅ Gallery selection fallback
4. ✅ Photo persistence on page refresh
5. ✅ Quantity persistence on page refresh
6. ✅ Photo preview with retake option
7. ✅ Photo ready status indicator
8. ✅ Automatic photo resize and compression
9. ✅ localStorage cleanup (7 days)
10. ✅ Clear all data on "Start New"

## 📝 Notes

- Photos are stored as base64 in localStorage
- Each employee has separate storage
- Storage is date-specific (today's date)
- Photos compressed to ~60% quality for speed
- Maximum 2MB per photo file
