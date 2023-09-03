<?php


namespace Mmantai\DbCore;

use Mmantai\DbCore\DbCore;
use MMantai\QueryBuilder\MySQLQueryBuilder;
use MMantai\QueryBuilder\QueryBuilderFactory;
use PDO;
use PDOException;

class MySQLDB
{
    private PDO $pdo;

    private string $db;

    private string $host;

    private string $user;

    private string $pw;

    private MySQLQueryBuilder $queryBuilder;

    function __construct(string $db, string $host, string $user, string $pw) 
    {
        $this->db = $db;
        $this->host = $host;
        $this->user = $user;
        $this->pw = $pw;
        $this->queryBuilder = QueryBuilderFactory::create("mysql");
        
        try {
            $this->pdo = new PDO('mysql:dbname='.$this->db.';host='.$this->host.';', $this->user, $this->pw);
            // var_dump($this->pdo);
        }
        catch(PDOException $e) {
            //error logging
        }
    }

    public function select(array $fields, string $table, string $tableAlias = "", array $join = array(), array $where = array(), array $orderBy = array(), array $order = array(), array $groupBy = array())
    {
        $res = [
            "result" => ""
        ];

        $this->queryBuilder->select($fields);
        $this->queryBuilder->from($table, $tableAlias);

        //joins
        if(count($join) > 0)
        {
            foreach($join as $j)
            {
                $joinType = $j[0];
                array_shift($j);
                switch($joinType)
                {
                    case "INNER JOIN": $this->queryBuilder->innerJoin(...$j);
                    break;
                    case "LEFT JOIN": $this->queryBuilder->leftJoin(...$j);
                    break;
                    case "RIGHT JOIN": $this->queryBuilder->rightJoin(...$j);
                    break;
                    case "FULL JOIN": $this->queryBuilder->fullJoin(...$j);
                    break;
                    case "NARURAL JOIN": $this->queryBuilder->naturalJoin(...$j);
                    break;
                }
            }
        }

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

        if(count($groupBy) > 0)
        {
            $this->queryBuilder->groupBy($groupBy);
        }

        if(count($orderBy) != count($order))
        {
            //throw error
        }
        else if(count($orderBy) > 0 && count($order) > 0)
        {
            $this->queryBuilder->orderBy($orderBy, $order);
        }
        
        try
        {
            $query = $this->queryBuilder->get();
            //print $query;
            $sql = $this->pdo->prepare($query);
            $sql->execute();
            $res["result"] = $sql->fetch(PDO::FETCH_ASSOC);

        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }

    public static function initDB(string $dbName, string $host, string $user, string $pw): array 
    {
        $res = [
            "result" => ""
        ];

        try 
        {
            $tmpConn = new PDO('mysql:dbname=;host='.$host.';', $user, $pw);
            $dbName = filter_var($dbName);
            $sql_tmp = $tmpConn->prepare("CREATE db IF NOT EXISTS $dbName");
            $res = $sql_tmp->execute();
        }
        catch(PDOException $e) 
        {
            die("Error creating db");
        }

        return $res;
    }

    /**
     * @return void
     */
    function closeConnection(): void 
    {
        $this->pdo = null;
    }

    public static function initTables(string $dir): array 
    {
        $res = [
            "result" => ""
        ];
        
        $tableFiles = glob($dir . '*.sql');
        try {
            $tmp = new PDO('mysql:dbname='. self::$db .';host='. self::$host .';', self::$user, self::$pw);
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

    public static function dbExists(string $dbName, string $host, string $user, string $pw): array
    {
        $res = [
            "result" => "success"
        ];

        $query = "SHOW DATABASES LIKE ?";

        try 
        {
            $tmpPDO = new PDO('mysql:dbname=;host='. $host .';', $user, $pw);
            $sql = $tmpPDO->prepare($query);
            $sql->execute([$dbName]);
        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }

    public function begin() 
    {
        $this->pdo->beginTransaction();
    }

    public function commit() 
    {
        $this->pdo->commit();
    }

    public function rollback() 
    {
        $this->pdo->rollBack();
    }
}