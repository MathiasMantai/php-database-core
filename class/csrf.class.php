<?php
//csrf protection class
class Csrf {

    private $csrf_key;
    private $csrf_time; 

    function __construct() {

    }

    public function getCsrfKey() {
        return $csrf_key;
    }

    public function setCsrfTokenField() {
        print '<input type="text" name='.$csrf_key.' value='.$csrf_key.'>';
    }
}

?>