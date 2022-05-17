<?php
//csrf protection class
class Csrf {

    private $csrf_token;
    private $csrf_time; 

    function __construct() {
        $csrf_token = generateCsrfToken(10);
    }

    public function getCsrfKey() {
        return $csrf_token;
    }

    private function generateCsrfToken($len) {
        return bin2hex(random_bytes($len));
    }

    public function setCsrfTokenField() {
        print '<input type="text" name='.$csrf_token.' value='.$csrf_token.'>';
    }
}

?>