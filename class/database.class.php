<?php

class DB {

    private $pdo;

    function __construct() {
        try {
            $this->pdo = new PDO('mysql:dbname='.DATABASE.';host='.DBHOST.';',DBUSER,DBPW);
            // var_dump($this->pdo);
        }
        catch(PDOException $e) {
            print $e;
        }
    }

    static function initDB() {
        try {
            $tmp = new PDO('mysql:dbname=;host='.DBHOST.';',DBUSER,DBPW);
            $sql_tmp = $tmp->prepare('CREATE DATABASE IF NOT EXISTS rb_database');
            $sql_tmp->execute();
        }
        catch(PDOException $e) {
            print $e;
        }
    }

    function closeConnection() {
        $this->pdo = null;
    }

    static function initTables() {
        $tableFiles = glob('./src/sql/'.PREFIX.'*.sql');
        try {
            $tmp = new PDO('mysql:dbname='.DATABASE.';host='.DBHOST.';',DBUSER,DBPW);
            for($i = 0; $i < count($tableFiles); $i++) {
                $fileData = file_get_contents($tableFiles[$i]);
                $sql = $tmp->prepare($fileData);
                $sql->execute();
            }
        }
        catch(PDOException $e) {
            print $e;
            die;
        }
    }

    function select($query, $mode, $bindArray) {
        $sql = $this->pdo->prepare($query);
        $sql->execute($bindArray);
        switch($mode) {
            case 'fetchRow':
                return $sql->fetch();
            case 'fetchAll':
                return $sql->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    function insert($table,$columns, $values) {
        global $PREFIX;
        $query = 'INSERT INTO '.$table.' ';
        $query .= '( ';
        for($i = 0; $i < count($columns); $i++) {
            $query .= '?';
            if($i < count($columns)-1) {
                $query .= ', ';
            }
        }
        $query .= ' ) VALUES ( ';
        for($i = 0; $i < count($values); $i++) {
            $query .= $values[$i];
            if($i < count($values)-1) {
                $query .= ', ';
            }
        }
        $query .= ' )';
        // print $query;
        // var_dump($this->pdo);
        try {
            $sql = $this->pdo->prepare($query);
            $sql->execute($values);
        }
        catch(PDOException $e) {
            print $e;
        }
    }

    function update($query, $queryParameters = []) {
        try {
            $sql = $this->pdo->prepare($query);
            $sql->execute($queryParameters);
        }
        catch(PDOException $e) {
            print $e;
        }
    }

    function delete($query, $queryParameters = []) {
        try {
            $sql = $this->pdo->prepare($query);
            $sql->execute($queryParameters);
        }
        catch(PDOException $e) {
            print $e;
        }
    }
}

?>