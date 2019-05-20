<?php 

  class Test {
    public function getHome()
    {
      http_response_code(201);
      echo json_encode([
        "status"=> 'OK',
        "message" => "You are in Home page",
      ]);
    }
    public function getMain()
    {
      http_response_code(201);
      echo json_encode([
        "status"=> 'OK',
        "message" => "We are in another page",
      ]);
    }
  }