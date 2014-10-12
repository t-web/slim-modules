<?php

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

require 'imported' . DIRECTORY_SEPARATOR . 'ImportedModule.php';

try
{    
    $config = array
    (
//        'slim.dir.root' => __DIR__,
//        'slim.dir.modules' => __DIR__ . DIRECTORY_SEPARATOR . 'modules',
//        'slim.dir.templates' => __DIR__ . DIRECTORY_SEPARATOR . 'templates',
        'slim.modules' => array
        (
            'imported' => '\Test\ImportedModule'
        )
    );

    $app = new \BenGee\Slim\Modules\SlimMod($config);

    $app->run();
}
catch (\Exception $e)
{
    echo '<h1>SlimMod test page</h1>';
    echo '<p>An exception occurred while testing !</p>';
    echo '<div><pre>' . $e . '</pre></div>';
}
