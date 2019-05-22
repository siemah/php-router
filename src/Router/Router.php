<?php 
 
  namespace DayenIO\Router;

  class Router  
  {
    
    protected $http_method;
    protected $baseSrcPath='';
    protected $mainDirectory = '/';
    protected $_instance = null;

    function __construct() {
      $this->init();
    }
    
    /**
     * initialize a some params of this Router
     */
    protected function init(): void
    {
      $this->http_method = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * change a current directory where we load a classes 
     * used as a callback on method to send a response (get, post ..)
     * @param {String} $path the new path to direcroy source
     * @return Router instance
     */
    public function setBaseSrcPath(String $path = ''): Router
    {
      $this->baseSrcPath = $path;
      return $this;
    }

    /**
     * config a main directory where will look to import classes
     * @param {String} $directory the path to new main directory
     * @return Router instance
     */
    public function setMainDirectory(string $directory = '/'): Router {
      $this->mainDirectory = $directory;
      return $this;
    }
    
    /** 
     * handle all GET request
     * @param {String} $path the path hitted by user
     * @param {Mixed} $callback the callback called to handle request
     * @throw an error if this function called outside of GET request
     */
    public function get (string $path, $callback): void {
      if( $this->http_method === 'GET' ) {
        $this->map($path, $callback);
      } else throw new Exception("You can call this function if the Request method is not a GET request");
    }

    /**
     * this is a process to handlling a user request
     * @param {String} $path the path hitted by user
     * @param {Mixed} $callback the callback called to handle request
     * @return null
     */
    protected function map ($path, $callback): void {
      $class = '';
      $method = '';
      //var_dump("{$_SERVER['REQUEST_URI']}", "{$this->mainDirectory}{$path}");
      //var_dump(gettype($callback));
      if(
        in_array(
          $_SERVER['REQUEST_URI'], 
          [
            "{$this->mainDirectory}{$path}",
            "{$this->mainDirectory}{$path}/"
          ]
        )
       ) {
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
            } else {
              call_user_func($callback);
            }
            return;
          case 'object': $callback();return;
          default:
            echo 'not a string func';
            break;
        }
        return;
      }
    }

  }
  
