<?php
echo "=== ImageMagick Check ===\n";
echo "ImageMagick binary: " . (shell_exec('which convert') ? 'FOUND' : 'NOT FOUND') . "\n";
echo "PHP imagick extension: " . (extension_loaded('imagick') ? 'LOADED' : 'NOT LOADED') . "\n";

if (extension_loaded('imagick')) {
    $imagick = new Imagick();
    echo "Imagick version: " . $imagick->getVersion()['versionString'] . "\n";
    
    // Test creating a simple image
    try {
        $imagick->newImage(100, 100, 'white');
        $imagick->setImageFormat('png');
        echo "Imagick test: SUCCESS - Can create images\n";
    } catch (Exception $e) {
        echo "Imagick test: FAILED - " . $e->getMessage() . "\n";
    }
} else {
    echo "Cannot test imagick - extension not loaded\n";
}

echo "=== End Check ===\n";
?>