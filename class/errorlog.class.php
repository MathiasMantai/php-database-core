<?php

class ErrorLog {
    function __construct() {

    }
    public function logError($errorMessage) {
        $file = fopen('../log/error_log.log', 'a');
        fwrite($file, '\n');
        fwrite($file, $errorMessage);
        fclose($file);
    }
}

?>