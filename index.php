<?php 

  require 'src/Router/Router.php';
  
  $router = new DayenIO\Router\Router();
  $router
    ->setBaseSrcPath(__DIR__ . '/src')
    ->setMainDirectory('/php-router');

  
  function call()
  {
    echo "we are /home hehehe";
  }

  $router->get('/', 'Controller/Test.getHome');
  $router->get('/home', 'call');
  $router->get('/about', function () {
    $render = file_get_contents('src/Views/about.html');
    header('Content-Type: text/html');
    echo $render;
  });
  $router->get('*', function ()
  {
    echo "404";
  });

  
  $class = "Test";
  $method = "getHome";
  //$reflection = new \ReflectionMethod($class, $method);
  //echo $reflection->invoke(new $class());

?>

