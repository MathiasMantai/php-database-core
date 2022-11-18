<?php


namespace DbCore;
use DbCore\ErrorLog;
use DbCore\Csrf;
use PDO;


class DbCore {

    private PDO $pdo;

    private ErrorLog $errorLog;

    private string $database;

    private string $host;

    private string $user;

    private string $password;

    function __construct(string $database, string $host, string $user, string $password) {
        $this->database = $database;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        try {
            $this->pdo = new PDO('mysql:dbname='.$this->database.';host='.$this->host.';', $this->user, $this->password);
            // var_dump($this->pdo);
        }
        catch(PDOException $e) {
            print $e;
        }
        $this->errorLog = new ErrorLog();
    }


    /**
     * @param string $dbName
     * @return boolean
     */
    static function initDB(string $dbName): boolean {
        $res;
        try {
            $tmpConn = new PDO('mysql:dbname=;host='.$this->host.';', $this->user, $this->password);
            $sql_tmp = $tmpConn->prepare('CREATE DATABASE IF NOT EXISTS ?');
            $res = $sql_tmp->execute([$dbName]);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }

        return $res;
    }

    /**
     * @return void
     */
    function closeConnection(): void {
        $this->pdo = null;
    }

    /**
     * @param string $dir
     * @return boolean
     */
    static function initTables(string $dir): boolean  {
        $res;
        $tableFiles = glob($dir . '*.sql');
        try {
            $tmp = new PDO('mysql:dbname='. $this->database .';host='. $this->host .';', $this->user, $this->password);
            for($i = 0; $i < count($tableFiles); $i++) {
                $fileData = file_get_contents($tableFiles[$i]);
                $sql = $tmp->prepare($fileData);
                $res = $sql->execute();
            }
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
            die;
        }

        return $res;
    }

    /**
     * @param string $query
     * @param string $mode
     * @param array $bindArray
     * @return mixed
     */
    function select(string $query, string $mode, array $bindArray): mixed {
        $sql = $this->pdo->prepare($query);
        $sql->execute($bindArray);
        switch($mode) {
            case 'fetchRow':
                return $sql->fetch();
            case 'fetchAll':
                return $sql->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * @param string $table
     * @param array $columns
     * @param array $values
     * @return boolean
     */
    function insert(string $table,array $columns,array $values): boolean {
        $res;
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

        return $res;
    }

    /**
     * @param string $query
     * @param array $bindArray
     * @return boolean
     */
    function update(string $query, array $bindArray = []): boolean {
        $res;
        try {
            $sql = $this->pdo->prepare($query);
            $res = $sql->execute($bindArray);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }

        return $res;
    }

    /**
     * @param string $query
     * @param array $bindArray
     * @return boolean
     */
    function delete($query, $bindArray = []): boolean {
        $res;
        try {
            $sql = $this->pdo->prepare($query);
            $res = $sql->execute($queryParameters);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }
        return $res;
    }
}