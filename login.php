<?php
require_once 'config.php';
require_once 'auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        try {
            $db = Database::getInstance()->getConnection();
            if (login($username, $password, $db)) {
                header('Location: index.php');
                exit;
            } else {
                $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    } else {
        $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#C4161C">
    <title>เข้าสู่ระบบ - Hazel Stock Management</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 2rem;
        }
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            padding: 2rem;
        }
        .login-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
            display: block;
            border-radius: 50%;
            border: 4px solid #dc2626;
        }
        .login-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 0.5rem;
        }
        .login-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 0.875rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s;
        }
        .form-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 0.875rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
        }
        .btn-back {
            width: 100%;
            background: #f3f4f6;
            color: #6b7280;
            padding: 0.75rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background: #e5e7eb;
        }
        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <img src="assets/hazel-logo.png" alt="Hazel" class="login-logo">
            <h1 class="login-title">Hazel Stock Management</h1>
            <p class="login-subtitle">เข้าสู่ระบบแอดมิน</p>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-select" 
                           placeholder="admin"
                           required 
                           autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">รหัสผ่าน</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-select" 
                           placeholder="••••••••"
                           required 
                           autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn-login">🔐 เข้าสู่ระบบ</button>
                <a href="index.php" class="btn-back" style="display: block; text-align: center; text-decoration: none;">← กลับหน้าหลัก</a>
            </form>
            
            <!-- Install App Button -->
            <div id="installAppSection" style="display: none; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                <button id="installAppBtn" onclick="installPWA()" style="
                    width: 100%;
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                    color: white;
                    padding: 0.875rem;
                    border: none;
                    border-radius: 0.5rem;
                    font-size: 1rem;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.2s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                ">
                    <span style="font-size: 1.5rem;">📱</span>
                    <span>ติดตั้งแอปบนมือถือ</span>
                </button>
                <p style="text-align: center; font-size: 0.75rem; color: #6b7280; margin-top: 0.5rem;">
                    ใช้งานได้เหมือนแอปจริง ไม่ต้องเปิดเบราว์เซอร์
                </p>
            </div>
            
            <!-- iOS Install Instructions -->
            <div id="iosInstructions" style="display: none; margin-top: 1.5rem; padding: 1rem; background: #fef3c7; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: #92400e; margin-bottom: 0.5rem;">
                    📱 วิธีติดตั้งบน iPhone
                </h3>
                <ol style="font-size: 0.75rem; color: #78350f; line-height: 1.6; margin: 0; padding-left: 1.25rem;">
                    <li>กดปุ่ม <strong>แชร์</strong> 📤 ที่ด้านล่าง</li>
                    <li>เลื่อนหา <strong>"เพิ่มที่หน้าจอโฮม"</strong></li>
                    <li>กด <strong>"เพิ่ม"</strong> เพื่อยืนยัน</li>
                </ol>
            </div>
        </div>
    </div>
    
    <!-- PWA Install Script -->
    <script>
        let deferredPrompt;
        
        // Check if already installed
        function isPWAInstalled() {
            return window.matchMedia('(display-mode: standalone)').matches || 
                   window.navigator.standalone === true;
        }
        
        // Check if iOS
        function isIOS() {
            return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        }
        
        // Show install button
        function showInstallButton() {
            if (isPWAInstalled()) {
                console.log('[PWA] Already installed');
                return;
            }
            
            if (isIOS()) {
                // Show iOS instructions
                document.getElementById('iosInstructions').style.display = 'block';
            } else if (deferredPrompt) {
                // Show install button for Android/Desktop
                document.getElementById('installAppSection').style.display = 'block';
            }
        }
        
        // Install PWA
        async function installPWA() {
            if (!deferredPrompt) {
                alert('ไม่สามารถติดตั้งได้ในขณะนี้');
                return;
            }
            
            // Show install prompt
            deferredPrompt.prompt();
            
            // Wait for user response
            const { outcome } = await deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted installation');
                document.getElementById('installAppSection').style.display = 'none';
                
                // Show success message
                const successMsg = document.createElement('div');
                successMsg.style.cssText = `
                    position: fixed;
                    top: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #10b981;
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 0.5rem;
                    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                    z-index: 9999;
                    font-weight: 600;
                `;
                successMsg.textContent = '✅ ติดตั้งสำเร็จ! ตรวจสอบหน้าจอหลักของคุณ';
                document.body.appendChild(successMsg);
                
                setTimeout(() => successMsg.remove(), 3000);
            } else {
                console.log('[PWA] User dismissed installation');
            }
            
            deferredPrompt = null;
        }
        
        // Listen for beforeinstallprompt
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('[PWA] beforeinstallprompt fired');
            e.preventDefault();
            deferredPrompt = e;
            showInstallButton();
        });
        
        // Check on page load
        window.addEventListener('load', () => {
            // Show iOS instructions immediately if on iOS and not installed
            if (isIOS() && !isPWAInstalled()) {
                document.getElementById('iosInstructions').style.display = 'block';
            }
            
            // For Android/Desktop, wait for beforeinstallprompt event
            setTimeout(() => {
                if (deferredPrompt && !isPWAInstalled()) {
                    showInstallButton();
                }
            }, 1000);
        });
        
        // Listen for app installed
        window.addEventListener('appinstalled', () => {
            console.log('[PWA] App installed successfully');
            document.getElementById('installAppSection').style.display = 'none';
            document.getElementById('iosInstructions').style.display = 'none';
        });
    </script>
</body>
</html>
