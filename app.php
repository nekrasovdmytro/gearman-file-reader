<?php

include "vendor/autoload.php";

use FileSystem\BaseFileSystem;

try {

    if (empty($argv[1])) {
        throw new Exception("Directory is not set \n");
    }

    // dir is setting in console
    $dir = $argv[1];


    $gmclient= new GearmanClient();
    $gmclient->addServer();

    $complete = function($task)
    {
        print "COMPLETE: " . $task->unique() . ", " . $task->data() . "\n";
    };

    $gmclient->setCompleteCallback($complete);

    $fileSystem = new BaseFileSystem();
    $filesArray = $fileSystem->getFiles($dir);

    //create tasks
    for ($i = 0; $i < count($filesArray); $i++) {
        $gmclient->addTask("read",serialize($filesArray[$i]) , null, $i);
    }

    if (!$gmclient->runTasks()) {
        echo "ERROR " . $gmclient->error() . "\n";
        exit;
    }

} catch(Exception $e) {
    $e->getMessage();
}