<?php 
 
  namespace DayenIO\Router;

  class Router  
  {
    
    protected $http_method;
    protected $baseSrcPath='';

    function __construct()    {
      $this->http_method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * change a current directory where we load a classes 
     * used as a callback on method to send a response (get, post ..)
     * @param {String} $path the new path to direcroy source
     */
    public function setBaseSrcPath(String $path = '')
    {
      $this->baseSrcPath = $path;
    }
    

    public function get ($path, $callback) {
      $class = '';
      $method = '';

      if($this->http_method === 'GET') {
        switch ( gettype($callback) ) {
          case 'string':
            $pointPosition = strpos($callback, '.');
            if( $pointPosition ) { 
              $class = substr($callback, 0, $pointPosition);
              $method = substr($callback,$pointPosition+1);
              require "{$this->baseSrcPath}/$class.php";
              $class = substr($class, strpos($class, '/')+1);
              $reflection = new \ReflectionMethod($class, $method);
              $reflection->invoke(new $class());
            }
            break;
          
          default:
            echo 'not a string func';
            break;
        }
      }
    }




  }
  
