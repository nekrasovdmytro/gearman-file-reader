<?php

include "vendor/autoload.php";

use FileReader\FileReader;

try {
    $worker = new GearmanWorker();

    $worker->addServer();

    $readProcess = function ($filesArray)
    {
        $filesArray = unserialize($filesArray->workload());

        $fileReader = new FileReader();
        $fileReader->setSource($filesArray);
        $fileReader->read();
    };

    $worker->addFunction("read", $readProcess);

    while (true) {
        print "Waiting for job...\n";
        $ret = $worker->work(); // work() will block execution until a job is delivered
        if ($worker->returnCode() != GEARMAN_SUCCESS) {
            break;
        }
    }



} catch(Exception $e) {
    $e->getMessage();
}