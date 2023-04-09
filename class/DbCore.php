<?php


namespace Mmantai\DbCore;

use Mmantai\DbCore\ErrorLog;
use Mmantai\QueryBuilder\QueryBuilderFactory;
use PDO;


abstract class DbCore 
{

    private PDO $pdo;

    private ErrorLog $errorLog;

    private QueryBuilderInterface $queryBuilder;

    private string $database;

    private string $host;

    private string $user;

    private string $password;

    /**
     * @return void
     */
    public function closeConnection(): void 
    {
        $this->pdo = null;
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