<?php
require_once __DIR__ . "/../config/cors.php";

$url = $_SERVER["REQUEST_URI"];
$method = $_SERVER["REQUEST_METHOD"];

if (strpos($url, 'chat') !== false && $method == "POST") {
  header("Content-Type: application/json");
  require_once __DIR__ . "/../routes/chat.php";
} elseif ($url === '/swagger' || $url === '/swagger/' || $url === '/swagger.php' || $url === '/swagger.json') {
  if ($url === '/swagger.json') {
    header("Content-Type: application/json");
    require_once __DIR__ . "/swagger.php";
  } else {
    header("Content-Type: text/html; charset=utf-8");
    readfile(__DIR__ . "/../docs/index.html");
  }
} else {
  header("Content-Type: application/json");
  echo json_encode([
    "status" => "online",
    "url_recebida" => $url,
    "metodo" => $method,
    "dica" => "Envie um POST para /chat ou acesse /swagger para ver a documentação"
  ]);
}
