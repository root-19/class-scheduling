<?php
// Source logo path
$sourceLogo = __DIR__ . '/app/Resources/image/logo/scheduling-logo.png';

// Create icons directory if it doesn't exist
$iconsDir = __DIR__ . '/app/Resources/image/icons';
if (!file_exists($iconsDir)) {
    mkdir($iconsDir, 0777, true);
}

// Icon sizes to generate
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Load the source image
$source = imagecreatefrompng($sourceLogo);
if (!$source) {
    die("Failed to load source image");
}

// Generate each icon size
foreach ($sizes as $size) {
    // Create a new image with the target size
    $icon = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($icon, false);
    imagesavealpha($icon, true);
    $transparent = imagecolorallocatealpha($icon, 255, 255, 255, 127);
    imagefilledrectangle($icon, 0, 0, $size, $size, $transparent);
    
    // Resize the source image
    imagecopyresampled(
        $icon,
        $source,
        0, 0, 0, 0,
        $size, $size,
        imagesx($source), imagesy($source)
    );
    
    // Save the icon
    $outputPath = $iconsDir . "/icon-{$size}x{$size}.png";
    imagepng($icon, $outputPath);
    imagedestroy($icon);
    
    echo "Generated icon: {$size}x{$size}\n";
}

// Generate maskable icons (with padding)
foreach ([192, 512] as $size) {
    // Create a new image with the target size
    $icon = imagecreatetruecolor($size, $size);
    
    // Fill with transparent background
    imagealphablending($icon, false);
    imagesavealpha($icon, true);
    $transparent = imagecolorallocatealpha($icon, 255, 255, 255, 127);
    imagefilledrectangle($icon, 0, 0, $size, $size, $transparent);
    
    // Calculate padding (20% of the size)
    $padding = $size * 0.2;
    $innerSize = $size - ($padding * 2);
    
    // Resize the source image with padding
    imagecopyresampled(
        $icon,
        $source,
        $padding, $padding, 0, 0,
        $innerSize, $innerSize,
        imagesx($source), imagesy($source)
    );
    
    // Save the maskable icon
    $outputPath = $iconsDir . "/maskable-icon-{$size}x{$size}.png";
    imagepng($icon, $outputPath);
    imagedestroy($icon);
    
    echo "Generated maskable icon: {$size}x{$size}\n";
}

// Clean up
imagedestroy($source);

echo "All icons generated successfully!\n";
?> 