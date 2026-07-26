<?php
class GoogleGeminiService
{
  private function getDb()
  {
    $host = getenv("DB_HOST") ?: "mysql";
    $port = getenv("DB_PORT") ?: "3306";
    $database = getenv("DB_DATABASE") ?: "chatbot";
    $username = getenv("DB_USERNAME") ?: "chatbot";
    $password = getenv("DB_PASSWORD") ?: "chatbot_password";

    $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

    try {
      return new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (PDOException $e) {
      return null;
    }
  }

  private function getProdutosContext()
  {
    $pdo = $this->getDb();

    if (!$pdo) {
      return "";
    }

    try {
      $stmt = $pdo->query("SELECT nome, categoria, descricao, preco, estoque FROM produtos WHERE ativo = 1 ORDER BY nome LIMIT 10");
      $produtos = $stmt->fetchAll();
    } catch (PDOException $e) {
      return "";
    }

    if (empty($produtos)) {
      return "";
    }

    $linhas = [];
    foreach ($produtos as $produto) {
      $linhas[] = sprintf(
        "- %s | categoria: %s | preço: R$ %.2f | estoque: %d | descrição: %s",
        $produto["nome"],
        $produto["categoria"],
        (float) $produto["preco"],
        (int) $produto["estoque"],
        $produto["descricao"]
      );
    }

    return "Produtos disponíveis no catálogo:\n" . implode("\n", $linhas);
  }

  private function respostaLocal($mensagem)
  {
    $pdo = $this->getDb();
    if (!$pdo) {
      return "No momento não consigo consultar o catálogo, mas posso ajudar com outras informações.";
    }

    $mensagemLower = mb_strtolower($mensagem, 'UTF-8');

    $palavraChave = null;
    foreach (["shampoo", "condicionador", "sabonete", "creme", "hidratante", "revitalizante", "facial", "corporal"] as $palavra) {
      if (strpos($mensagemLower, $palavra) !== false) {
        $palavraChave = $palavra;
        break;
      }
    }

    if ($palavraChave !== null) {
      $stmt = $pdo->prepare("SELECT nome, preco, estoque FROM produtos WHERE ativo = 1 AND LOWER(nome) LIKE :nome ORDER BY nome LIMIT 5");
      $stmt->execute(["nome" => '%' . $palavraChave . '%']);
      $produtos = $stmt->fetchAll();

      if (!empty($produtos)) {
        $primeiro = $produtos[0];

        if (strpos($mensagemLower, 'preço') !== false || strpos($mensagemLower, 'custa') !== false || strpos($mensagemLower, 'valor') !== false) {
          return "O produto " . $primeiro['nome'] . " custa R$ " . number_format((float) $primeiro['preco'], 2, ',', '.') . ".";
        }

        if (strpos($mensagemLower, 'estoque') !== false || strpos($mensagemLower, 'disponível') !== false || strpos($mensagemLower, 'disponivel') !== false) {
          return "O produto " . $primeiro['nome'] . " está com " . (int) $primeiro['estoque'] . " unidades em estoque.";
        }

        return "Encontrei " . $primeiro['nome'] . " por R$ " . number_format((float) $primeiro['preco'], 2, ',', '.') . " e com " . (int) $primeiro['estoque'] . " unidades em estoque.";
      }
    }

    if (strpos($mensagemLower, 'preço') !== false || strpos($mensagemLower, 'custa') !== false || strpos($mensagemLower, 'valor') !== false) {
      $stmt = $pdo->prepare("SELECT nome, preco FROM produtos WHERE ativo = 1 ORDER BY nome");
      $stmt->execute();
      $produtos = $stmt->fetchAll();

      if (!empty($produtos)) {
        $lista = [];
        foreach ($produtos as $produto) {
          $lista[] = $produto['nome'] . ': R$ ' . number_format((float) $produto['preco'], 2, ',', '.');
        }
        return "Aqui estão os preços disponíveis no catálogo da Alves:\n- " . implode("\n- ", $lista);
      }
    }

    if (strpos($mensagemLower, 'estoque') !== false || strpos($mensagemLower, 'disponível') !== false || strpos($mensagemLower, 'disponivel') !== false) {
      $stmt = $pdo->prepare("SELECT nome, estoque FROM produtos WHERE ativo = 1 ORDER BY nome");
      $stmt->execute();
      $produtos = $stmt->fetchAll();

      if (!empty($produtos)) {
        $lista = [];
        foreach ($produtos as $produto) {
          $lista[] = $produto['nome'] . ': ' . (int) $produto['estoque'] . ' unidades';
        }
        return "Estoque disponível no momento:\n- " . implode("\n- ", $lista);
      }
    }

    $stmt = $pdo->prepare("SELECT nome, preco, estoque FROM produtos WHERE ativo = 1 ORDER BY nome LIMIT 5");
    $stmt->execute();
    $produtos = $stmt->fetchAll();

    if (!empty($produtos)) {
      $lista = [];
      foreach ($produtos as $produto) {
        $lista[] = $produto['nome'] . ' — R$ ' . number_format((float) $produto['preco'], 2, ',', '.') . ' — estoque: ' . (int) $produto['estoque'];
      }
      return "Posso te ajudar com produtos da Alves. Alguns itens disponíveis são:\n- " . implode("\n- ", $lista);
    }

    return "Ainda não há produtos cadastrados para consulta.";
  }

  public function enviar($mensagem)
  {
    $apiKey = getenv("GEMINI_API_KEY") ?: "";
    $model = getenv("GEMINI_MODEL") ?: "gemini-2.0-flash";

    if (empty($apiKey)) {
      return ["resposta" => "Erro: GEMINI_API_KEY não configurada."];
    }

    $contexto = $this->getProdutosContext();
    $prompt = $mensagem;

    $persona = "Você é um atendente virtual do Alves. Responda de forma acolhedora, profissional e objetiva. " .
      "Sempre que o cliente pedir informações sobre produtos, use o catálogo abaixo para responder com base nos dados reais. " .
      "Se não encontrar o produto ou não houver informação, diga com honestidade e ofereça ajuda para procurar algo semelhante.";

    if (!empty($contexto)) {
      $prompt = $persona . "\n\nCatálogo da Alves:\n" . $contexto . "\n\nPergunta do cliente: " . $mensagem;
    } else {
      $prompt = $persona . "\n\nPergunta do cliente: " . $mensagem;
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
    $dados = [
      "contents" => [[
        "parts" => [["text" => $prompt]]
      ]]
    ];

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dados));
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
      return ["resposta" => "Erro Gemini: " . $err];
    }

    $resultado = json_decode($response, true);

    $textoResposta = "";

    if (!empty($resultado["candidates"][0]["content"]["parts"])) {
      foreach ($resultado["candidates"][0]["content"]["parts"] as $part) {
        if (!empty($part["text"])) {
          $textoResposta .= $part["text"];
        }
      }
    }

    if (!empty($textoResposta)) {
      return ["resposta" => trim($textoResposta)];
    }

    if (!empty($resultado["error"]["message"])) {
      return ["resposta" => $this->respostaLocal($mensagem)];
    }

    return ["resposta" => $this->respostaLocal($mensagem)];
  }
}
