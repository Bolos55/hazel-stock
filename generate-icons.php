<?php
/**
 * Generate PWA Icons from Logo
 * 
 * This script generates all required PWA icon sizes from the Hazel logo
 * Run: php generate-icons.php
 */

// Icon sizes needed for PWA
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Source logo
$sourceLogo = 'assets/hazel-logo.png';

// Output directory
$outputDir = 'icons';

// Create icons directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "✅ Created icons directory\n";
}

// Check if source logo exists
if (!file_exists($sourceLogo)) {
    die("❌ Error: Source logo not found at {$sourceLogo}\n");
}

// Check if GD library is available
if (!extension_loaded('gd')) {
    die("❌ Error: GD library is not installed. Please install php-gd extension.\n");
}

echo "🎨 Generating PWA icons from {$sourceLogo}...\n\n";

// Load source image
$sourceImage = @imagecreatefrompng($sourceLogo);
if (!$sourceImage) {
    die("❌ Error: Could not load source image\n");
}

// Get source dimensions
$sourceWidth = imagesx($sourceImage);
$sourceHeight = imagesy($sourceImage);

echo "📐 Source image: {$sourceWidth}x{$sourceHeight}\n\n";

// Generate each icon size
foreach ($sizes as $size) {
    $outputFile = "{$outputDir}/icon-{$size}x{$size}.png";
    
    // Create new image with transparency
    $newImage = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    
    // Fill with transparent background
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);
    
    // Enable alpha blending for copying
    imagealphablending($newImage, true);
    
    // Resize and copy image
    imagecopyresampled(
        $newImage,
        $sourceImage,
        0, 0, 0, 0,
        $size, $size,
        $sourceWidth, $sourceHeight
    );
    
    // Save PNG
    if (imagepng($newImage, $outputFile, 9)) {
        $fileSize = filesize($outputFile);
        $fileSizeKB = round($fileSize / 1024, 2);
        echo "✅ Generated: {$outputFile} ({$fileSizeKB} KB)\n";
    } else {
        echo "❌ Failed: {$outputFile}\n";
    }
    
    // Free memory
    imagedestroy($newImage);
}

// Free source image memory
imagedestroy($sourceImage);

echo "\n🎉 Icon generation complete!\n";
echo "📁 Icons saved in: {$outputDir}/\n\n";

// Generate favicon.ico (16x16 and 32x32)
echo "🔖 Generating favicon.ico...\n";

// Note: Creating .ico files requires additional libraries
// For now, we'll create a 32x32 PNG as favicon
$faviconSource = imagecreatefrompng($sourceLogo);
$favicon = imagecreatetruecolor(32, 32);

imagealphablending($favicon, false);
imagesavealpha($favicon, true);
$transparent = imagecolorallocatealpha($favicon, 0, 0, 0, 127);
imagefill($favicon, 0, 0, $transparent);
imagealphablending($favicon, true);

imagecopyresampled(
    $favicon,
    $faviconSource,
    0, 0, 0, 0,
    32, 32,
    imagesx($faviconSource), imagesy($faviconSource)
);

if (imagepng($favicon, 'favicon.png', 9)) {
    echo "✅ Generated: favicon.png (32x32)\n";
}

imagedestroy($favicon);
imagedestroy($faviconSource);

echo "\n✨ All done! Your PWA icons are ready.\n";
echo "\n📝 Next steps:\n";
echo "1. Add manifest.json link to all HTML pages\n";
echo "2. Register service worker in JavaScript\n";
echo "3. Test with Lighthouse\n";
echo "4. Deploy with HTTPS\n";
