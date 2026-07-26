<?php
require_once __DIR__ . "/../services/GoogleGeminiService.php";

class ChatController
{
  public function perguntar()
  {
    $input = file_get_contents("php://input");
    $dados = json_decode($input, true);

    if (!isset($dados["mensagem"])) {
      echo json_encode(["erro" => "Mensagem não enviada"]);
      return;
    }

    $gemini = new GoogleGeminiService();
    $resposta = $gemini->enviar($dados["mensagem"]);

    echo json_encode($resposta);
  }
}
