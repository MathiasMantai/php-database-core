<?php


namespace Mmantai\DbCore;

use Mmantai\DbCore\DbCore;
use Mmantai\DbCore\ErrorLog;
use Mmantai\QueryBuilder\QueryBuilderFactory;
use PDO;

/**
 * class for mysql databases using PDO
 */
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
     * method for building and executing select statements
     * @param array $fields     table fields to select
     * @param string $table     table to select from
     * @param string $tableAlias
     * @param array $join
     * @param array $where      where statement. structure should be [[column, operator, value], ["AND", column, operator, value]]
     */
    public function select(array $fields, string $table, string $tableAlias = "", array $join = array(), array $where = array(), array $orderBy = array(), string $order = "", array $groupBy = array())
    {
        $this->queryBuilder->select($fields);
        $this->queryBuilder->from($table, $tableAlias);

        //joins

        //where
        $this->queryBuilder->where(...$where[0]);
        //rest of where
        $cnt = count($where);
        for($i = 1; $i < $cnt; $i++)
        {
            if(strtoupper($where[$i]) == "AND")
                $this->queryBuilder->and(...$where[$i]);
            else if(strtoupper($where[$i]) == "OR")
                $this->queryBuilder->or(...$where[$i]);
        }

        //group by
        $this->queryBuilder->groupBy($groupBy);

        //order by
        $this->queryBuilder->orderBy($orderBy, $order);
        
        
        try
        {
            print $this->queryBuilder->getQuery();
            $sql = $this->pdo->prepare($this->queryBuilder->getQuery());
            $sql->execute();
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            return $res;
        }
        catch(PDOException $e)
        {
            die($e->getMessage());
        }
    }


    /**
     * @param string $dbName
     * @return bool
     */
    public static function initDB(string $dbName, string $host, string $user, string $password): bool 
    {
        $res;
        try 
        {
            $tmpConn = new PDO('mysql:dbname=;host='.$host.';', $user, $password);
            $dbName = filter_var($dbName);
            $sql_tmp = $tmpConn->prepare("CREATE DATABASE IF NOT EXISTS $dbName");
            $res = $sql_tmp->execute();
        }
        catch(PDOException $e) 
        {
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