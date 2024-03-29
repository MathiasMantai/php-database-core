<?php

namespace Mmantai\DbCore;

use Mmantai\QueryBuilder\MySQLQueryBuilder;
use Mmantai\QueryBuilder\QueryBuilderFactory;
use PDO;
use PDOException;

class MySQLDB
{
    private PDO|null $pdo;

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

    public function getEmptyResultObject()
    {
        return [
            "query",
            "result" => "success"
        ];
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
            $res["result"] = $sql_tmp->execute();
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

    public function select(array $fields, string $table, string $tableAlias = "", array $join = array(), array $where = array(), array $orderBy = array(), array $order = array(), array $groupBy = array())
    {
        $res = $this->getEmptyResultObject();

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
            if(strtoupper($where[$i][0]) == "AND")
                $this->queryBuilder->and(...array_slice($where[$i], 1, 3));
            else if(strtoupper($where[$i][0]) == "OR")
                $this->queryBuilder->or(...array_slice($where[$i], 1, 3));
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
            $sql = $this->pdo->prepare($query);
            $sql->execute();
            $res["result"] = $sql->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return false;
        }

        return $res;
    }

    private function getPlaceholderArray(int $amount)
    {
        return array_fill(0, $amount, "?");
    }

    public function insert(string $table, array $columns, array $values)
    {
        $res = $this->getEmptyResultObject();

        try
        {
            $valueCnt = count($values);
            $query = $this->queryBuilder->insert($table, $columns, $this->getPlaceholderArray($valueCnt))->get();
            $res["query"] = $query;
            $sql = $this->pdo->prepare($query);
            $sql->execute($values);
        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }

    public function update(string $table, array $columns, array $values, array $where = [])
    {
        $res = $this->getEmptyResultObject();

        try
        {
            $cntC = count($columns); 
            $cntV = count($values);

            if($cntC != $cntV)
            {
                $res["result"] = "Update error: column and value amount does not match";
                return $res;
            }
            else if($cntC == 0)
            {
                $res["result"] = "Update error: column array cannot be empty";
                return $res;
            }
            else if($cntV == 0)
            {
                $res["result"] = "Update error: value array cannot be empty";
                return $res;
            }

            $cnt = count($columns);

            $this->queryBuilder->update($table);

            //set
            if($cnt > 1)
            {
                $this->queryBuilder->setMulti($columns, $this->getPlaceholderArray($cnt));
            }
            else 
            {
                $this->queryBuilder->set($columns[0], "?");
            }


            //where
            $cntW = count($where);

            if($cntW > 0)
            {
                if(is_array($where[0]))
                {
                    $firstW = $where[0];
                    $this->queryBuilder->where($firstW[0], $firstW[1], "?");
                    array_push($values, $firstW[2]);

                    array_shift($where);

                    foreach($where as $w)
                    {
                        if($w[0] == "AND")
                        {
                            $this->queryBuilder->and($w[1], $w[2], "?");
                            array_push($values, $w[3]);
                            
                        }
                        else if($w[0] == "OR")
                        {
                            $this->queryBuilder->or($w[1], $w[2], "?");
                            array_push($values, $w[3]);
                        }
                        else
                        {
                            $res["result"] = "Update error: unknown operator found in where array";
                        }
                    }
                }
                else
                {
                    $this->queryBuilder->where($where[0], $where[1], "?");
                    array_push($values, $where[2]);
                }
            }

            $query = $this->queryBuilder->get();
            $res["query"] = $query;
            $sql = $this->pdo->prepare($query);
            $sql->execute($values);
        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }

    public function delete(string $table, array $where = [])
    {
        $res = $this->getEmptyResultObject();
        
        $values = [];

        $this->queryBuilder->delete($table);

        try
        {
            $cntW = count($where);

            if($cntW > 0)
            {
                if(is_array($where[0]))
                {
                    $firstW = $where[0];
                    $this->queryBuilder->where($firstW[0], $firstW[1], "?");
                    array_push($values, $firstW[2]);

                    array_shift($where);

                    foreach($where as $w)
                    {
                        if($w[0] == "AND")
                        {
                            $this->queryBuilder->and($w[1], $w[2], "?");
                            array_push($values, $w[3]);
                            
                        }
                        else if($w[0] == "OR")
                        {
                            $this->queryBuilder->or($w[1], $w[2], "?");
                            array_push($values, $w[3]);
                        }
                        else
                        {
                            $res["result"] = "Update error: unknown operator found in where array";
                        }
                    }
                }
                else
                {
                    $this->queryBuilder->where($where[0], $where[1], "?");
                    array_push($values, $where[2]);
                }
            }

            $query = $this->queryBuilder->get();
            $res["query"] = $query;
            $sql = $this->pdo->prepare($query);
            $sql->execute($values);
        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }

    public function query(string $query, array $values = []): array
    {
        $res = $this->getEmptyResultObject();

        try
        {
            $res["query"] = $query;
            $sql = $this->pdo->prepare($query);
            $sql->execute($values);

            if(strpos(strtoupper($query), "SELECT") !== false)
            {
                $res["result"] = $sql->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        catch(PDOException $e)
        {
            $res["result"] = $e->getMessage();
        }

        return $res;
    }
}