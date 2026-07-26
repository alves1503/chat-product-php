# Chatbot Alves

Projeto full-stack de um chatbot de atendimento desenvolvido com PHP, MySQL e React/Vite, com integração à API do Gemini para responder perguntas sobre produtos e fornecer uma experiência de suporte mais próxima de um atendente real.

## Visão geral

Este projeto simula um atendimento comercial inteligente para a marca Alves. A aplicação permite:

- conversar com um assistente virtual;
- consultar informações de produtos a partir de um catálogo em banco de dados;
- responder perguntas como preço, disponibilidade e características;
- servir uma documentação interativa da API via Swagger.

## Stack utilizada

### Backend
- PHP 8.2 + Apache
- PDO com MySQL
- Rotas REST para conversa
- Integração com Gemini

### Frontend
- React + Vite
- CSS moderno para interface de chat

### Infraestrutura
- Docker Compose
- MySQL 8
- Variáveis de ambiente para configuração

## Como o projeto foi desenvolvido

A solução foi construída em camadas para separar responsabilidades:

1. Backend PHP
   - Recebe as mensagens do frontend.
   - Consulta o catálogo de produtos no banco MySQL.
   - Envia contexto à API do Gemini para gerar respostas mais naturais.
   - Possui fallback local caso a API externa não esteja disponível.

2. Banco de dados
   - A tabela de produtos armazena nome, preço, estoque e descrição.
   - A tabela de mensagens registra conversas para acompanhamento.

3. Frontend React
   - Exibe uma interface simples e funcional de chat.
   - Envia mensagens para a API e mostra as respostas do atendente virtual.

4. Documentação
   - A API possui uma documentação Swagger interativa acessível em /swagger.

## Estrutura do projeto

```text
projeto-chatbot/
├── backend/
│   └── api-rest/
│       ├── controllers/
│       ├── public/
│       ├── routes/
│       ├── services/
│       └── database/
├── frontend/
├── docker-compose.yml
├── .env
└── README.md
```

## Pré-requisitos

Antes de executar o projeto, certifique-se de ter instalado:

- Docker Desktop
- Docker Compose
- Git

## Como testar localmente

### 1. Clone o repositório

```bash
git clone <seu-repositorio>
cd projeto-chatbot
```

### 2. Configure as variáveis de ambiente

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

Edite o arquivo .env com suas credenciais e chave da API do Gemini.

### 3. Suba os containers

```bash
docker compose up -d --build
```

### 4. Acesse a aplicação

- Frontend: http://localhost:5173
- API: http://localhost:8000
- Swagger: http://localhost:8000/swagger

## Exemplo de uso

No chat, você pode testar mensagens como:

- Qual o preço do Sabonete Facial?
- Vocês têm estoque do shampoo hidratante?
- Me fale sobre o creme corporal

## Variáveis de ambiente

O projeto utiliza as seguintes variáveis:

```env
MYSQL_ROOT_PASSWORD=
MYSQL_DATABASE=
MYSQL_USER=
MYSQL_PASSWORD=
GEMINI_API_KEY=
GEMINI_MODEL=
VITE_API_URL=
```

## Como criar uma chave do Gemini gratuitamente

Para testar a integração com IA localmente, você pode gerar uma chave gratuita na plataforma do Google AI Studio.

### Passo a passo

1. Acesse o Google AI Studio:
   - https://aistudio.google.com/

2. Faça login com sua conta Google.

3. No painel, clique em "Get API key" ou "Create API key".

4. Crie uma nova chave de API.

5. Copie a chave gerada.

6. No projeto, cole a chave no arquivo .env, na variável:

```env
GEMINI_API_KEY=sua_chave_aqui
```

7. Se quiser, use o modelo padrão abaixo:

```env
GEMINI_MODEL=gemini-2.0-flash
```

> Importante: algumas chaves podem ter limites de uso gratuito, então para testes simples o fluxo funciona bem. Se a chave não estiver disponível ou exceder o limite, o projeto já possui um fallback local para responder com base no catálogo.

