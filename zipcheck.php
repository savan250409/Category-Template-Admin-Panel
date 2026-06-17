<?php
require __DIR__.'/vendor/autoload.php';
$mt = \Symfony\Component\Mime\MimeTypes::getDefault();
foreach (($argv) as $i=>$f) {
    if ($i===0) continue;
    if (!is_file($f)) { echo "missing: $f\n"; continue; }
    $fi = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fi, $f);
    $exts = $mt->getExtensions($mime);
    $uf = new \Symfony\Component\HttpFoundation\File\UploadedFile($f, basename($f), null, null, true);
    $guess = $uf->guessExtension();
    echo basename($f).": mime=$mime guessExt=".var_export($guess,true)." mimes:zip ".(in_array($guess,['zip'])?'PASS':'FAIL')."\n";
}
