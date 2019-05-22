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
     * @return {Mixed} List if there is data to get or associate Array or 
     * false in case there not data to retrieve  
     */
    public function query(string $sql, $fetchAll=true): array {
      $query = $this
                ->getPdo()
                ->query($sql);
      return $fetchAll
              ? $query->fetchAll() 
              : $query->fetch();
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
        $fieldsWhereJoin = implode(" =? {$options['operation']} ", $options['whereFields']);
        $fieldsWhereJoin .= " =? ";
        $sql .= " WHERE $fieldsWhereJoin "; 
      }

      $sth = $this->getPdo()->prepare($sql);
      $sth->execute($options['whereFieldsValues']);

      return $fetchAll 
              ? $sth->fetchAll() 
              : $sth->fetch();
    }
    
  }