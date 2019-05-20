<?php 
 
  namespace DayenIO\Router;

  class Router  
  {
    
    protected $http_method;
    protected $baseSrcPath='';
    protected $mainDirectory = '/';

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
      return $this;
    }

    public function setMainDirectory(String $directory = '/')
    {
      $this->mainDirectory = $directory;
    }
    

    public function get ($path, $callback) {
      $class = '';
      $method = '';
      //var_dump("{$_SERVER['REQUEST_URI']}", "{$this->mainDirectory}{$path}");
      //var_dump(gettype($callback));
      if(
        $this->http_method === 'GET' AND
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
  
