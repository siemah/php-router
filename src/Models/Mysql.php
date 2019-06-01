<?php

  class Mysql {

    protected $host='localhost';
    protected $dbname=null;
    protected $user=null;
    protected $password=null;
    protected $_dbInstance=null;

    function __construct(string $host, string $dbname, string $user, string $password) {
      $this->init($host,$dbname,$user,$password);
    }

    /**
     * initialize some variables and stuffs
     * @param {array} $array contain some details about DB permission
     */
    protected function init(string ...$array) {
      $this->host = $array[0];
      $this->dbname = $array[1];
      $this->user = $array[2];
      $this->password = $array[3];
      // avoid creation each time a connection to PDO
      if( !isset($this->_dbInstance) ) {
        $this->setPdo();
      } 

    }

    /**
     * get the connection instace to db
     * @return PDO
     */
    protected function getPdo(): PDO {
      return $this->_dbInstance;
    }

    /**
     * set a connection to DB
     */
    protected function setPdo(): void {
      try {
          $this->_dbInstance = new PDO(
            "mysql:dbname={$this->dbname};host={$this->host}", 
            $this->user, 
            $this->password
          );
        } catch (\Throwable $th) {
          throw $th;
        }
    }

    /**
     * query DB by $sql request and retrieve all/one data
     * @param {String} $sql the sql request support MySQL sql
     * @param {Boolean} $fetchAll get all data or one depend on true or false
     * @param {Array} list of options like where fields values use when prepare a query
     * @return {Mixed} List if there is data to get or associate Array or 
     * false in case there not data to retrieve  
     */
    public function query(string $sql, $fetchAll=true, array $options=[]) {
      
      $sth = $this
                ->getPdo()
                ->prepare($sql);

      $values = count($options)? $options['whereFieldsValues'] : [];
      $exec = $sth->execute($values);

      if( preg_match("#^SELECT.+$#i", $sql) )
        return $fetchAll
                ? $sth->fetchAll() 
                : $sth->fetch();
      else if( preg_match("#^INSERT.+$#i", $sql) )
        return $exec;
      else if( preg_match("#^UPDATE.+$#i", $sql) )
        return $exec;
    }
    
    /**
     * retrieve all $fields data from $table 
     * is a short cut of SELECT query in SQL 
     * without write a whole query in SQL
     * @param {Array} $fields list of fields data to retrieve
     * @param {String} $table the naùe of table on DB
     * @param {Boolean} $fetchAll check if to fetch all data or get one row
     * @param {Array} $options contain some options 
     * like where clause on SQL query and there 3 values
     * the first is 'whereFields' array of fields to add on where clause
     * the 2nd 'whereFieldsValues' array of fields values (is a values of whereFields)
     * the 3th 'operation' list (AND, OR)
     */
    public function find(array $fields, string $table, bool $fetchAll=TRUE, array $options=[]): array  {

      $fieldsjoin = implode(', ', $fields);
      $fieldsWhereJoin = '';
      $sql = "SELECT $fieldsjoin FROM $table";

      if(count($options)) {
        $operation = isset($options['operation'])? $options['operation'] : '';
        $fieldsWhereJoin = implode(" =? {$operation} ", $options['whereFields']);
        $fieldsWhereJoin .= " =? ";
        $sql .= " WHERE $fieldsWhereJoin "; 
      }
      // old process
      //$sth = $this->getPdo()->prepare($sql);
      //$sth->execute($options['whereFieldsValues']);
      //using query function
      $whereFieldsValues = isset($options['whereFieldsValues'])? $options['whereFieldsValues'] : [];
      return $this->query($sql, $fetchAll, [ 'whereFieldsValues' => $whereFieldsValues ]);
    }

    /**
     * insert data $values of $fields into table $table
     * @param {String} $table name of table where insert data
     * @param {Array} $fields list of fields to insert
     * @param {Array} $values list of values of $fields to insert
     * @return {Bool} true if inserted otherwise false 
     */
    public function insert($table, $fields, $values){
    
      $sql = 'INSERT INTO ' . htmlentities($table) . ' ';
      $sql .=  '( ' . implode($fields, ', ') . ' )';
      $sql .= ' VALUES ( ';
      foreach ($fields as $key => $value) {
        $sql .= '?';
        if(($key+1) < count($fields)) $sql .= ', ';  
      }
      $sql .= ' )';
      $res = $this->query($sql, true, [
        'whereFieldsValues' => $values
      ]);

      return $res;
      
    }

    /**
     * insert data $values of $fields into table $table
     * @param {String} $table name of table where insert data
     * @param {Array} $fields list of fields to insert
     * @param {Array} $values list of values of $fields to insert
     * @return {Bool} true if inserted otherwise false 
     */
    public function update(string $table, array $fields, array $values, array $options=[]) {
      if( count($fields) === 0 OR count($values) === 0 ) 
        throw new Exception('Fields and/or values array is empty, you can\'t update this table because there is not field to update');
      else if( strlen(trim($table)) === 0 )
        throw new Exception('Table name must not to be an empty string');
      // construct a main sql query
      $sql = 'UPDATE ' . htmlentities($table) . ' SET ';
      foreach ($fields as $key => $value) {
        $sql .= htmlentities($value) . '=?';
        if(($key+1) < count($fields)) $sql .= ', ';  
      }
      // handle a where queries
      if( count($options) AND isset($options['whereFields'], $options['whereValues']) ) {
        if( count($options['whereValues']) !== count($options['whereFields']) )
          throw new Exception('Where fields array must contain the same length than where values array');
        $sql .= " WHERE ";
        foreach ($options['whereFields'] as $key => $value) {
          $sql .= htmlentities($value) . '=? ';
          if(($key+1) < count($fields) AND isset($options['operation']) AND count($options['whereFields']) > 1) 
            $sql .= $options['operation'] . " ";  
        }
        $values = array_merge($values, $options['whereValues']);
      }
      //var_dump($sql, $values);die;

      $res = $this->query($sql, true, [ 'whereFieldsValues' => $values ]);

      return $res;
      
    }
    
  }