CREATE DATABASE IF NOT EXISTS chatbot;

USE chatbot;

CREATE TABLE IF NOT EXISTS mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(100) NOT NULL,
    mensagem TEXT NOT NULL,
    resposta TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL,
    preco DECIMAL(10, 2) NOT NULL,
    estoque INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO
    mensagens (usuario, mensagem, resposta)
VALUES (
        'João',
        'Olá, como funciona o atendimento?',
        'Olá João! Posso ajudar com dúvidas sobre o chatbot e o atendimento.'
    ),
    (
        'Maria',
        'Quero saber mais sobre o plano',
        'Claro! Posso explicar os planos disponíveis e as condições.'
    ),
    (
        'Carlos',
        'Preciso de suporte',
        'Entendido. Vou encaminhar sua solicitação para o suporte adequado.'
    );

INSERT INTO
    produtos (
        nome,
        categoria,
        descricao,
        preco,
        estoque
    )
VALUES (
        'Shampoo Hidratante',
        'Higiene',
        'Shampoo para cabelos secos com hidratação intensa.',
        29.90,
        15
    ),
    (
        'Condicionador Revitalizante',
        'Higiene',
        'Condicionador que fortalece os fios e reduz a quebra.',
        31.50,
        10
    ),
    (
        'Sabonete Facial',
        'Beleza',
        'Sabonete suave para limpeza diária da pele.',
        19.90,
        25
    ),
    (
        'Creme Corporal',
        'Beleza',
        'Creme nutritivo com aloe vera e vitamina E.',
        24.90,
        20
    );