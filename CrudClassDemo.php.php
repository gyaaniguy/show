<?php

/**
 * Created by PhpStorm.
 * User: aa
 * Date: 10-Jun-19
 * Time: 8:27 PM
 *
 * Class to implement common database CRUD functions - create read update delete. Work with any table .
 * Also included helper classes that should be moved to separate files in a real app.
 */

interface DatabaseInterface
{
    public function execute($sqlString, $colNameValues, $colTypes);

    public function fetch(PDOStatement $stmt);

    public function affectedRows(PDOStatement $stmt);

    public function lastInsertId();

    public function bindStuff($colNameValues, $colTypes, PDOStatement $stmt): void;
}
class DatabaseWrapper implements DatabaseInterface
{
    private $pdo;

    /**
     * DbClass constructor.
     * @param $pdo
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    function affectedRows(PDOStatement $stmt)
    {
        return $stmt->rowCount();
    }

    function execute($sql, $colNameValues, $colTypes)
    {
        $stmt = $this->pdo->prepare($sql);
        $this->bindStuff($colNameValues, $colTypes, $stmt);
        $result = $stmt->execute();
        return [$result, $stmt];
    }

    /**
     * @param $colNameValues
     * @param $colTypes
     * @param $stmt
     */
    public function bindStuff($colNameValues, $colTypes, PDOStatement $stmt): void
    {
        foreach ($colNameValues as $columnName => $value) {
            $stmt->bindValue($columnName, $value, $colTypes[$columnName]);
        }
    }

    public function fetch(PDOStatement $stmt)
    {
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

class CrudClass
{
    protected $db;
    protected $tableName;
    protected $allColumns;

    /**
     * Generic class with CRUD insert create update delete functions.
     * CrudClass constructor.
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param $colNameValues
     *  In format colName => Value
     * @return mixed
     *
     */
    function update($colNameValues)
    {
        $sql = 'UPDATE ' . $this->tableName . ' SET ';
        foreach ($colNameValues as $col => $value) {
            $sql .= ' `' . $col . '` = :' . $col . ' , ';
        }
        $sql = substr($sql, 0, -2);
        list($result, $stmt) = $this->db->execute($sql, $colNameValues, $this->allColumns);
        if ($result) {
            return $this->db->affectedRows($stmt);
        }
        return $result;
    }

    /**
     * Returns all rows for id
     * @param $id
     * @return mixed
     */
    function fetchForId($id)
    {
        $sql = ' Select * FROM ' . $this->tableName;
        $sql .= ' WHERE id = :id';
        list($result, $stmt) = $this->db->execute($sql, ['id' => $id], $this->allColumns);
        if ($result) {
            $rows = $this->db->fetch($stmt);
            return $rows;
        }
        return $result;
    }

    /**
     * @param $columns
     * Array of columns to insert values in
     * @param $values
     * Multidimensional array of values to insert corresponding to the first column parameter
     * @return mixed
     */
    function insert($columns, $values)
    {

        $colCount = 1;
        $colTypes = $colNameValues = [];

        $sql = ' INSERT INTO ' . $this->tableName;
        $sql .= '( ' . implode(' , ', $columns) . ' ) VALUES ';

        foreach ($values as $keyOuter => $row) {
            $sql .= '( ';

            // Construct bindIndex => values array
            foreach ($row as $keyRow => $val) {
                $sql .= ' ?, ';
                $colNameValues[$colCount++] = $val;
            }
            //populate numbered column types for binding purposes
            foreach ($columns as $column) {
                $colTypes[$colCount] = $this->allColumns[$column];
            }

            $sql .= substr($sql, 0, -2) . ' ), ';
        }

        $sql = substr($sql, 0, -2);

        list($result, $stmt) = $this->db->execute($sql, $colNameValues, $colTypes);
        if ($result) {
            return $this->db->lastInsertId();
        }
        return $result;
    }

    /**
     * Delete for ID
     * @param $id
     * @return mixed
     * stuff
     */
    function delete($id)
    {

        $sql = 'DELETE FROM ' . $this->tableName;
        $sql .= ' WHERE id = :id';
        list($result, $stmt) = $this->db->execute($sql, ['id' => $id], $this->allColumns);
        if ($result) {
            $rows = $this->db->fetch($stmt);
            return $rows;
        }
        if ($result) {
            return $this->db->affectedRows($stmt);
        }
        return $result;
    }

}


/*
 * SAMPLE USAGE
 */
class Comments extends CrudClass
{

    protected $tableName = 'comments';
    protected $allColumns = ['id' => PDO::PARAM_INT, 'text' => PDO::PARAM_STR, 'email' => PDO::PARAM_STR, 'name' => PDO::PARAM_STR, 'date_created' => PDO::PARAM_STR];

    /**
     * Comments constructor.
     * @param DatabaseInterface $db
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct($db);
    }

}
$pdo = new PDO('DSNSTRING');
$db = new DatabaseWrapper($pdo);
$comments = new Comments($db);
$comments->fetchForId(1);