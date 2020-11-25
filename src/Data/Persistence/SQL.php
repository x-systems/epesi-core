<?php 

namespace Epesi\Core\Data\Persistence;

use atk4\dsql\Connection;
use Illuminate\Database\DatabaseManager;

class SQL extends \atk4\data\Persistence\Sql
{
    /**
     * Take a laravel connection and pass it to ATK Data
     *
     * @return \atk4\data\Persistence\SQL
     */
    public function __construct(DatabaseManager $database)
    {
    	$pdo = $database->connection()->getPdo();
    	
    	// temporary fix of atk4/data inability to handle PREPARE on 'is null'
    	$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

    	parent::__construct(Connection::connect($pdo));
    }
}
