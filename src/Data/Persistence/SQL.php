<?php 

namespace Epesi\Core\Data\Persistence;

use atk4\dsql\Connection;
use Illuminate\Database\DatabaseManager;

class SQL extends \atk4\data\Persistence\SQL
{
    /**
     * Take a laravel connection and pass it to ATK Data
     *
     * @param \Illuminate\Database\DatabaseManager $db The Laravel database manager
     *
     * @return \atk4\data\Persistence_SQL
     * @throws \atk4\data\Exception
     * @throws \atk4\dsql\Exception
     */
    public function __construct(DatabaseManager $database)
    {
    	$pdo = $database->connection()->getPdo();
    	
    	// temporary fix of atk4/data inability to handle PREPARE on 'is null'
    	$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

    	parent::__construct(Connection::connect($pdo));
    }
}
