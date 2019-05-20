<?php 

  require 'src/Router/Router.php';
  
  $router = new DayenIO\Router\Router();
  $router->setBaseSrcPath(__DIR__ . '/src');

  $router->get('/', 'Controller/Test.getHome');
  
  $class = "Test";
  $method = "getHome";
  //$reflection = new \ReflectionMethod($class, $method);
  //echo $reflection->invoke(new $class());

?>

