<?php

namespace Mmantai\DbCore;

class ErrorLog {
    function __construct() {

    }
    public function logError($errorMessage) {
        $file = fopen('../log/error_log.log', 'a');
        fwrite($file, '\n');
        fwrite($file, date('d-m-Y H:i:s') . " - " . $errorMessage);
        fclose($file);
    }
}

