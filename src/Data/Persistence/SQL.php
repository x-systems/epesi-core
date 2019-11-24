<?php 

namespace Epesi\Core\Data\Persistence;

use atk4\dsql\Connection;
use Illuminate\Database\DatabaseManager;

class SQL extends \atk4\data\Persistence_SQL
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
    	parent::__construct(Connection::connect($database->connection()->getPdo()));
    }
}