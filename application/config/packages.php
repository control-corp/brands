<?php

$packages = [];

foreach (new \FilesystemIterator('application/packages') as $f) {
    $packages[$f->getFilename()] = $f->getPathname();
}

return [
    'packages' => $packages + [
        'MicroDebug' => 'library/Micro/packages/MicroDebug',
    ]
];