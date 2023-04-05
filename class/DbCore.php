<?php


namespace Mmantai\DbCore;
use DbCore\ErrorLog;

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
        $this->errorLog = new ErrorLog();
        try {
            $this->pdo = new PDO('mysql:dbname='.$this->database.';host='.$this->host.';', $this->user, $this->password);
            // var_dump($this->pdo);
        }
        catch(PDOException $e) {
            $this->errorLog->logError($e->getMessage());
        }
    }


    /**
     * @param string $dbName
     * @return bool
     */
    static function initDB(string $dbName, string $host, string $user, string $password): bool {
        $res;
        try {
            $tmpConn = new PDO('mysql:dbname=;host='.$host.';', $user, $password);
            $dbName = filter_var($dbName);
            $sql_tmp = $tmpConn->prepare("CREATE DATABASE IF NOT EXISTS $dbName");
            $res = $sql_tmp->execute();
        }
        catch(PDOException $e) {
            die("Error creating database");
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
     * @return bool
     */
    static function initTables(string $dir): bool  {
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
            die("Error creating tables");
        }

        return $res;
    }

    /**
     * @param string $dbName name of the database to check
     * @param string $host host adress
     * @param string $user username
     * @param string $password password
     * @return bool true = database exists ; false = database does not exist
     */
    public static function dbExists(string $dbName, string $host, string $user, string $password): bool {
        $query = "SHOW DATABASES LIKE ?";
        $tmpPDO = new PDO('mysql:dbname=;host='. $host .';', $user, $password);
        $sql = $tmpPDO->prepare($query);
        $res = $sql->execute([$dbName]);
        return $res;
    }

    public function begin() {
        $this->pdo->beginTransaction();
    }

    public function commit() {
        $this->pdo->commit();
    }

    public function rollback() {
        $this->pdo->rollBack();
    }


    /**
     * @param string $query
     * @param string $mode
     * @param array $bindArray
     * @return array|bool
     */
    function select(string $query, string $mode, array $bindArray = []): array|bool {
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
     * @return bool
     */
    public function insert(string $table,array $columns,array $values):bool {

        $res;

        if(count($columns) != count($values)) return -1;

        //build the query
        $colString = implode(",", $columns);
        $bindString = implode(",",array_fill(0, count($values),"?"));


        $query = "INSERT INTO " . $table . "({$colString}) VALUES ({$bindString})";

        $this->begin();

        $sql = $this->pdo->prepare($query);
        $res = $sql->execute($values);

        if($res) {
            $this->commit();
        }
        else $this->rollback();
        return $res;
        
    }

    /**
     * @param string $query
     * @param array $bindArray
     * @return bool
     */
    function update(string $query, array $bindArray = []): bool {
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
     * @return bool
     */
    function delete($query, $bindArray = []): bool {
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