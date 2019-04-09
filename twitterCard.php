<?PHP
# stackexchange2php("https://stackoverflow.com/questions/15151051/url-to-a-random-image")
function random_pic($dir = 'imagerotation') {
    $files = glob($dir . '/*.jpg');
    if (!$files) return false;

    $file = array_rand($files);
    return $files[$file];
}
function outputImage( $filename ) {
    header('Content-Description: File Transfer');
    header("Content-type: image/jpeg");
#    header('Content-Disposition: attachment; filename='.basename($filename ));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename ));
    ob_clean();
    flush();
    readfile($filename );
    exit;
}
// Get a filename
$filename=random_pic();
// Check that a file was found
if (!$filename) {
    header('HTTP/1.0 404 Not Found');
    die();
}

// Output the image
outputImage( $filename );
?>
