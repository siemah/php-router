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
    
    
  }