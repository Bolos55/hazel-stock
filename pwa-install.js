// PWA Installation Handler for Hazel Stock Management
(function() {
    'use strict';
    
    let deferredPrompt;
    let installButton;
    
    // Check if already installed
    function isInstalled() {
        // Check if running in standalone mode
        if (window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        
        // Check if running as PWA on iOS
        if (window.navigator.standalone === true) {
            return true;
        }
        
        return false;
    }
    
    // Show install prompt
    function showInstallPrompt() {
        if (isInstalled()) {
            console.log('[PWA] Already installed');
            return;
        }
        
        // Create install banner
        const banner = document.createElement('div');
        banner.id = 'pwa-install-banner';
        banner.innerHTML = `
            <div style="
                position: fixed;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: linear-gradient(135deg, #C4161C 0%, #A01218 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 1rem;
                box-shadow: 0 8px 24px rgba(196, 22, 28, 0.4);
                z-index: 9999;
                max-width: 90%;
                width: 400px;
                display: flex;
                align-items: center;
                gap: 1rem;
                animation: slideUp 0.3s ease-out;
            ">
                <div style="font-size: 2rem;">📱</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">ติดตั้งแอป Hazel Stock</div>
                    <div style="font-size: 0.875rem; opacity: 0.9;">เข้าถึงได้ง่ายจากหน้าจอหลัก</div>
                </div>
                <button id="pwa-install-btn" style="
                    background: white;
                    color: #C4161C;
                    border: none;
                    padding: 0.5rem 1rem;
                    border-radius: 0.5rem;
                    font-weight: 600;
                    cursor: pointer;
                    font-size: 0.875rem;
                    white-space: nowrap;
                ">ติดตั้ง</button>
                <button id="pwa-close-btn" style="
                    background: transparent;
                    color: white;
                    border: none;
                    padding: 0.5rem;
                    cursor: pointer;
                    font-size: 1.25rem;
                    line-height: 1;
                ">✕</button>
            </div>
            <style>
                @keyframes slideUp {
                    from {
                        transform: translateX(-50%) translateY(100px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(-50%) translateY(0);
                        opacity: 1;
                    }
                }
            </style>
        `;
        
        document.body.appendChild(banner);
        
        // Install button click
        document.getElementById('pwa-install-btn').addEventListener('click', async () => {
            if (!deferredPrompt) {
                showIOSInstructions();
                return;
            }
            
            // Show install prompt
            deferredPrompt.prompt();
            
            // Wait for user response
            const { outcome } = await deferredPrompt.userChoice;
            console.log('[PWA] Install outcome:', outcome);
            
            if (outcome === 'accepted') {
                console.log('[PWA] User accepted installation');
            } else {
                console.log('[PWA] User dismissed installation');
            }
            
            // Clear deferred prompt
            deferredPrompt = null;
            
            // Remove banner
            banner.remove();
        });
        
        // Close button click
        document.getElementById('pwa-close-btn').addEventListener('click', () => {
            banner.remove();
            // Don't show again for 7 days
            localStorage.setItem('pwa-install-dismissed', Date.now());
        });
    }
    
    // Show iOS installation instructions
    function showIOSInstructions() {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        
        if (!isIOS) {
            alert('กรุณาใช้เบราว์เซอร์ที่รองรับการติดตั้ง PWA');
            return;
        }
        
        const modal = document.createElement('div');
        modal.innerHTML = `
            <div style="
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            " onclick="this.remove()">
                <div style="
                    background: white;
                    border-radius: 1rem;
                    padding: 2rem;
                    max-width: 400px;
                    text-align: center;
                " onclick="event.stopPropagation()">
                    <h3 style="color: #C4161C; margin-bottom: 1rem; font-size: 1.25rem;">วิธีติดตั้งบน iOS</h3>
                    <ol style="text-align: left; line-height: 1.8; color: #333;">
                        <li>กดปุ่ม <strong>แชร์</strong> 📤 ที่แถบด้านล่าง</li>
                        <li>เลื่อนลงหา <strong>"เพิ่มที่หน้าจอโฮม"</strong></li>
                        <li>กด <strong>"เพิ่ม"</strong> เพื่อยืนยัน</li>
                    </ol>
                    <button onclick="this.closest('div[style*=fixed]').remove()" style="
                        margin-top: 1.5rem;
                        background: #C4161C;
                        color: white;
                        border: none;
                        padding: 0.75rem 2rem;
                        border-radius: 0.5rem;
                        font-weight: 600;
                        cursor: pointer;
                    ">เข้าใจแล้ว</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    // Check if should show install prompt
    function shouldShowPrompt() {
        // Don't show if already installed
        if (isInstalled()) {
            return false;
        }
        
        // Check if dismissed recently (within 7 days)
        const dismissed = localStorage.getItem('pwa-install-dismissed');
        if (dismissed) {
            const daysSinceDismissed = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24);
            if (daysSinceDismissed < 7) {
                return false;
            }
        }
        
        return true;
    }
    
    // Listen for beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('[PWA] beforeinstallprompt event fired');
        
        // Prevent default prompt
        e.preventDefault();
        
        // Store event for later use
        deferredPrompt = e;
        
        // Show custom install prompt after 3 seconds
        if (shouldShowPrompt()) {
            setTimeout(showInstallPrompt, 3000);
        }
    });
    
    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
        console.log('[PWA] App installed successfully');
        deferredPrompt = null;
        
        // Show success message
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.remove();
        }
        
        // Optional: Show thank you message
        const thankYou = document.createElement('div');
        thankYou.innerHTML = `
            <div style="
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background: #10b981;
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 0.75rem;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
                z-index: 9999;
                animation: slideDown 0.3s ease-out;
            ">
                ✅ ติดตั้งสำเร็จ! ขอบคุณที่ใช้ Hazel Stock
            </div>
            <style>
                @keyframes slideDown {
                    from {
                        transform: translateX(-50%) translateY(-100px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(-50%) translateY(0);
                        opacity: 1;
                    }
                }
            </style>
        `;
        document.body.appendChild(thankYou);
        setTimeout(() => thankYou.remove(), 3000);
    });
    
    // Show install prompt on iOS after page load
    window.addEventListener('load', () => {
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
        const isInStandaloneMode = window.navigator.standalone === true;
        
        if (isIOS && !isInStandaloneMode && shouldShowPrompt()) {
            setTimeout(showInstallPrompt, 5000);
        }
    });
    
    console.log('[PWA] Install handler loaded');
})();
