<?php
/**
 * Clean Index - แก้ไขปัญหา encoding
 */

// Backup original file
if (file_exists('index.php')) {
    copy('index.php', 'index.php.backup');
    echo "✅ Backup created: index.php.backup\n";
}

// Read the file and clean it
$content = file_get_contents('index.php');

// Replace question marks with proper Thai text
$replacements = [
    // Error messages
    'ไฟล์รูปใหญ่เกินไป' => 'ไฟล์รูปใหญ่เกินไป',
    'กรุณาเลือกไฟล์รูปภาพ' => 'กรุณาเลือกไฟล์รูปภาพ',
    'กำลังประมวลผลรูป' => 'กำลังประมวลผลรูป',
    'กรุณารอสักครู่' => 'กรุณารอสักครู่',
    'เกิดข้อผิดพลาดในการประมวลผลรูป' => 'เกิดข้อผิดพลาดในการประมวลผลรูป',
    
    // PWA messages
    'มีเวอร์ชันใหม่! ต้องการอัพเดทหรือไม่?' => 'มีเวอร์ชันใหม่! ต้องการอัพเดทหรือไม่?',
    'ไม่สามารถติดตั้งได้ในขณะนี้' => 'ไม่สามารถติดตั้งได้ในขณะนี้',
    'กรุณาลองใหม่อีกครั้ง' => 'กรุณาลองใหม่อีกครั้ง',
    'ติดตั้งสำเร็จ!' => 'ติดตั้งสำเร็จ!',
    'ตรวจสอบไอคอนบนหน้าจอหลักของคุณ' => 'ตรวจสอบไอคอนบนหน้าจอหลักของคุณ'
];

// Clean up question marks pattern
$patterns = [
    '/\?{10,}/' => '', // Remove long sequences of question marks
    '/showError\(\'[?]+[^\']*\'\)/' => "showError('เกิดข้อผิดพลาด')", // Fix showError calls
    '/alert\(\'[?]+[^\']*\'\)/' => "alert('เกิดข้อผิดพลาด')", // Fix alert calls
];

foreach ($patterns as $pattern => $replacement) {
    $content = preg_replace($pattern, $replacement, $content);
}

foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Write cleaned content
file_put_contents('index-clean.php', $content);

echo "✅ Cleaned file created: index-clean.php\n";
echo "📝 Please review the cleaned file and replace index.php if it looks good.\n";
echo "🔧 To replace: rename index-clean.php to index.php\n";
?>