<?php
/**
 * Create Simple PWA Icons
 * Creates colored square icons with "H" letter
 */

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
$outputDir = 'icons';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "🎨 Creating PWA icons...\n\n";

foreach ($sizes as $size) {
    $image = imagecreatetruecolor($size, $size);
    
    // Hazel red color
    $red = imagecolorallocate($image, 196, 22, 28);
    $white = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background with red
    imagefill($image, 0, 0, $red);
    
    // Add white "H" letter
    $fontSize = $size * 0.5;
    $font = 5; // Built-in font
    
    // Calculate text position (centered)
    $text = 'H';
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $x = ($size - $textWidth) / 2;
    $y = ($size - $textHeight) / 2;
    
    // For larger sizes, use TTF font if available, otherwise use imagestring
    if ($size >= 192) {
        // Draw large H manually with rectangles
        $barWidth = $size * 0.15;
        $barHeight = $size * 0.7;
        $crossHeight = $size * 0.1;
        $margin = $size * 0.15;
        
        // Left vertical bar
        imagefilledrectangle($image, $margin, $margin, $margin + $barWidth, $margin + $barHeight, $white);
        
        // Right vertical bar
        imagefilledrectangle($image, $size - $margin - $barWidth, $margin, $size - $margin, $margin + $barHeight, $white);
        
        // Horizontal cross bar
        $crossY = $margin + ($barHeight - $crossHeight) / 2;
        imagefilledrectangle($image, $margin, $crossY, $size - $margin, $crossY + $crossHeight, $white);
    } else {
        // Use built-in font for smaller sizes
        imagestring($image, $font, $x, $y, $text, $white);
    }
    
    $outputFile = "{$outputDir}/icon-{$size}x{$size}.png";
    imagepng($image, $outputFile, 9);
    imagedestroy($image);
    
    $fileSize = round(filesize($outputFile) / 1024, 2);
    echo "✅ Created: {$outputFile} ({$fileSize} KB)\n";
}

// Create favicon
$favicon = imagecreatetruecolor(32, 32);
$red = imagecolorallocate($favicon, 196, 22, 28);
$white = imagecolorallocate($favicon, 255, 255, 255);
imagefill($favicon, 0, 0, $red);

$font = 5;
$text = 'H';
$x = (32 - imagefontwidth($font) * strlen($text)) / 2;
$y = (32 - imagefontheight($font)) / 2;
imagestring($favicon, $font, $x, $y, $text, $white);

imagepng($favicon, 'favicon.png', 9);
imagedestroy($favicon);

echo "✅ Created: favicon.png\n";
echo "\n🎉 All icons created successfully!\n";
