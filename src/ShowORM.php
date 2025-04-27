<?php

class ShowORM
{
    private static $instance;
    private static $table;
    private static $whereClause = '';
    private static $bindings = [];
    private static $limitClause = '';
    private static $pdo;
    private static $orderByClause = '';
    private static $groupByClause = '';
    private static $selectClause = '';

    private static $countClause='';

    public static function connect($host, $dbname, $user, $pass, $port , $charset)
    {
        $dsn = "mysql:host={$host};dbname={$dbname};port={$port};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            self::$pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function table($table)
    {
        self::$table = $table;
        self::$whereClause = '';
        self::$bindings = [];
        self::$limitClause = '';
        return new self;
    }

    public static function create_table($table, $columns)
    {
        $cols = [];
        foreach ($columns as $name => $type) {
            $cols[] = "`$name` $type";
        }
        $columnsSql = implode(', ', $cols);
        $sql = "CREATE TABLE IF NOT EXISTS `$table` ($columnsSql)";

        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute();
    }

    public function where($column, $operator, $value)
    {
        self::$whereClause = "WHERE {$column} {$operator} ?";
        self::$bindings[] = $value;
        return $this;
    }

    public function limit($number)
    {
        self::$limitClause = "LIMIT {$number}";
        return $this;
    }

    public function count($column = '*' , $as = '')
    {
        self::$countClause = "COUNT($column) AS {$as}";
        return $this;
    }

    public function select($columns = ['*'])
    {
        self::$selectClause = implode(', ', $columns);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        self::$orderByClause = "ORDER BY `$column` $direction";
        return $this;
    }
    public function groupBy($columns)
    {
        if (is_array($columns)) {
            self::$groupByClause = "GROUP BY " . implode(', ', $columns);
        } else {
            self::$groupByClause = "GROUP BY " . $columns;
        }
        return $this;
    }

    public function get($fetch)
    {
        $fetchMethod = ($fetch === 'fetch') ? 'fetch' : 'fetchAll';

        $select = self::$countClause ?: (self::$selectClause ?: '*');

        $sql = "SELECT {$select} FROM " . self::$table . " "
            . self::$whereClause . " "
            . self::$groupByClause . " "
            . self::$orderByClause . " "
            . self::$limitClause;

        $stmt = self::$pdo->prepare($sql);
        $stmt->execute(self::$bindings);
        return call_user_func([$stmt, $fetchMethod]);
    }

    public function find($id)
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function insert(array $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $values = array_values($data);

        $sql = "INSERT INTO " . self::$table . " ({$columns}) VALUES ({$placeholders})";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function update($id, array $data)
    {
        $fields = implode(' = ?, ', array_keys($data)) . ' = ?';
        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE " . self::$table . " SET {$fields} WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute($values);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM " . self::$table . " WHERE id = ?";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function delete_all()
    {
        $sql = "DELETE FROM " . self::$table ;
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute();
    }


}
