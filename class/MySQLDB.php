<?php


namespace Mmantai\DbCore;

use Mmantai\DbCore\DbCore;
use Mmantai\DbCore\ErrorLog;
use Mmantai\QueryBuilder\QueryBuilderFactory;
use PDO;


class MySQLDB extends DbCore 
{

    function __construct(string $database, string $host, string $user, string $password) 
    {
        $this->database = $database;
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->errorLog = new ErrorLog();
        $this->queryBuilder = QueryBuilderFactory::createQueryBuilder('mysql');
        
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
    public static function initDB(string $dbName, string $host, string $user, string $password): bool {
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
    public static function initTables(string $dir): bool  {
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
}