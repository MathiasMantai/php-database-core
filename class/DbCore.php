<?php


namespace DbCore;

class DbCore {

    private $pdo;
    private $errorLog;

    function __construct() {
        try {
            $this->pdo = new PDO('mysql:dbname='.DATABASE.';host='.DBHOST.';',DBUSER,DBPW);
            // var_dump($this->pdo);
        }
        catch(PDOException $e) {
            print $e;
        }
        $this->errorLog = new ErrorLog();
    }

    static function initDB() {
        try {
            $tmp = new PDO('mysql:dbname=;host='.DBHOST.';',DBUSER,DBPW);
            $sql_tmp = $tmp->prepare('CREATE DATABASE IF NOT EXISTS rb_database');
            $sql_tmp->execute();
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }
    }

    function closeConnection() {
        $this->pdo = null;
    }

    static function initTables() {
        $tableFiles = glob('../sql/'.PREFIX.'*.sql');
        try {
            $tmp = new PDO('mysql:dbname='.DATABASE.';host='.DBHOST.';',DBUSER,DBPW);
            for($i = 0; $i < count($tableFiles); $i++) {
                $fileData = file_get_contents($tableFiles[$i]);
                $sql = $tmp->prepare($fileData);
                $sql->execute();
            }
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
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
        $bindParameters = implode(',', array_fill(0, count($values, '?')));
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
            $this->errorLog->logError($e->getMessage());
        }
    }

    function update($query, $queryParameters = []) {
        try {
            $sql = $this->pdo->prepare($query);
            $sql->execute($queryParameters);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }
    }

    function delete($query, $queryParameters = []) {
        try {
            $sql = $this->pdo->prepare($query);
            $sql->execute($queryParameters);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }
    }
}

