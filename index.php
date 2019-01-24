<?php
#1
$echo = [];
for ($i = 1; $i <= 100; $i++) {
	$out = '';
	if (0 === $i%3) {
		$out = 'Fizz';
	}
	if (0 === $i%5) {
		$out .= 'Buzz';
	}
	if (!$out) {
		$out = $i;
	}
	
	$echo[] = $out;
}

echo implode(', ', $echo);

#2
$array = $const = range(1, 500);
shuffle($array);
unset($array[array_rand($array)]);
// 1.
echo array_sum($const) - array_sum($array);
// 2.
echo array_diff($const, $array);
// Or in for, this variants used minimum resources


#3
class MysqlDbConnection extends \mysqli
{
    public $table;

    public function insert($obj) {
        $values = [];
        foreach ($obj as $field => $value) {
            $values[$field] = $value;
        }
        $inc = "INSERT";
        if (isset($values['replace']) && $values['replace'] === TRUE) {
            unset($values['replace']);
            $inc = "REPLACE";
        }
        $query = "{$inc} INTO {$this->table} (" . implode(',', array_keys($values))
            . ") VALUES ('" . implode("','", $values) . "')";

        return $this->query($query);
    }

    public function getRows($fields = '*', $params = [], $start = 0, $count = 0) {
        if (is_array($fields) && count($fields)) $fields = implode(', ', $fields);
        $where = $limit = '';
        if ($count) $limit .= 'LIMIT ' . $start . ', ' . $count;
        if (is_array($params) && !empty($params)) {
            $where = [];
            foreach ($params as $key => $val) {
                $where[] = "$key = '{$val}'";
            }
            $where = "WHERE " . implode(' AND ', $where);
        }
        $query = "SELECT {$fields} FROM {$this->table} {$where} {$limit}";

        if (!$result = $this->query($query)) return FALSE;
        $out = [];
        while ($row = $result->fetch_object()) {
            $out[$row->id] = $row;
        }
        return $out;
    }
}

class DbConnection
{
    public function __construct($driver) {
        $this->config = Config::getDbConfig($driver);
        $this->driver = $driver;
    }
    /**
     * Run this private methods (mysql|mongo|others...)Connect()
     * @return object DbConnection
     */
    public function connect() {
        return $this->{"{$this->driver}Connect"}();
    }

    private function mysqlConnect() {
        $mysql = new MysqlDbConnection(
            $this->config->host,
            $this->config->user,
            $this->config->pass,
            $this->config->db
        );
        $mysql->set_charset('utf8');
        return $mysql;
    }

    private function mongoConnect() {}
}

class Db
{
    private static $instance;
    private $connections;

    private function __construct() {
        $this->connections = [];
    }

    public static function getInstance($driver = 'mysql') {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance->getConnection($driver);
    }

    private function getConnection($driver) {
        if (!isset($this->connections[$driver]) || !is_object($this->connections[$driver])) {
            $this->addConnection($driver);
        }
        return $this->connections[$driver];
    }

    private function addConnection($driver) {
        $this->connections[$driver] = (new DbConnection($driver))->connect();
    }

    private function __clone() {}
}

class Model
{
    public $Db;

    public function __construct()
    {
        $this->Db = Db::getInstance('mysql');
        $this->setDbTable();
    }
    public function setDbTable($table = FALSE)
    {
        if ($table) {
            $this->Db->table = $table;
        } else {
            $fullClassName = explode('\\', strtolower(get_class($this)));
            $this->Db->table = $fullClassName[count($fullClassName) - 1];
        }
    }
    public function addEntity($obj)
    {
        return $this->Db->insert($obj);
    }

    public function getEntitys($fields = '*', $params = [], $start = 0, $count = 0)
    {
        return $this->Db->getRows($fields, $params, $start, $count);
    }
}

class ExadsTest extends Model {
	public $name, $age, $job_title;
}

$ExadsTest = new ExadsTest();
$ExadsTest->setDbTable('exads_test');

return $ExadsTest->getEntitys($fields);

$ExadsTest = new ExadsTest();
$ExadsTest->name = htmlspecialchars($name);
$ExadsTest->age = (int)$age;
$ExadsTest->job_title = htmlspecialchars($job_title);
$ExadsTest->addEntity($ExadsTest);

#4
$periods     = [];
$optionality = '25.01.2019';

$optionality = $optionality ?? 'now';
$periods[date('l', strtotime($optionality))] = round(strtotime($optionality) - strtotime('today'));
$periods['Wednesday'] = round(strtotime('next Wednesday') - strtotime('now'));
$periods['Saturday']  = round(strtotime('next Saturday') - strtotime('now'));
asort($periods);
reset($periods);
$key = key($periods);
echo "next valid draw date:".$key.' '.date('d.m.Y', strtotime('next '.$key));


#5
$a = 50;
$b = 25;
$c = 25;
$total = "SELECT count(id) FROM users;";
$countA = $total * $a / 100;
$countB = $total * $b / 100;
$countC = $total * $c / 100;
$usersA = "SELECT * FROM users LIMIT 0, $countA"; 
$usersB = "SELECT * FROM users LIMIT $countA, $countB";
$usersC = "SELECT * FROM users LIMIT " . ($countA + $countB) . ", $countC";


