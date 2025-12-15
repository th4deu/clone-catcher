# 🔧 Corrigir Erros de Produção

## Erros Identificados:

### ❌ Erro 1: Tabela 'sessions' não existe
```
Table 'forge.sessions' doesn't exist
```

### ❌ Erro 2: sessionId está NULL
```
Column 'session_id' cannot be null
```

---

## ✅ SOLUÇÃO RÁPIDA

### 1. Conectar no servidor via SSH

```bash
ssh forge@cc-sorteador.on-forge.com
```

### 2. Navegar até o projeto

```bash
cd /home/forge/cc-sorteador.on-forge.com
```

### 3. Executar migrations (criar tabela sessions)

```bash
php artisan migrate --force
```

### 4. Limpar cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 5. Verificar se funcionou

```bash
php artisan migrate:status
```

Deve mostrar todas as migrations como "Ran".

---

## 🔧 CORRIGIR O PROBLEMA DO sessionId NULL

O erro acontece porque o JavaScript está enviando `sessionId` mas o servidor espera um nome diferente, ou o valor está vazio.

### Verificar o que está sendo enviado

Cole no Console do site clonado:

```javascript
// Interceptar o que está sendo enviado
const originalFetch = window.fetch;
window.fetch = function(...args) {
    if (args[0].includes('/api/collect')) {
        console.log('📤 Enviando para /api/collect:');
        console.log('Body:', args[1]?.body);

        // Parse do JSON para ver os dados
        try {
            const data = JSON.parse(args[1]?.body);
            console.log('📦 Dados parseados:', data);
            console.log('🆔 sessionId:', data.sessionId);
        } catch(e) {}
    }
    return originalFetch.apply(this, args);
};

console.log('✅ Interceptor instalado. Aguarde alguns segundos...');
```

---

## 🎯 SOLUÇÃO DEFINITIVA

O problema é que o campo `sessionId` pode estar:
1. Vazio/undefined no JavaScript
2. Com nome diferente do esperado

### Atualizar o Script JavaScript

Cole este código ATUALIZADO no GTM (substitua o antigo):

```javascript
<script>
(function() {
    'use strict';

    const COLLECTOR_URL = 'https://cc-sorteador.on-forge.com/api/collect';

    // Gera session ID único e SEMPRE válido
    function getSessionId() {
        let sessionId = sessionStorage.getItem('clone_tracker_session');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('clone_tracker_session', sessionId);
        }

        // GARANTIR que nunca seja null/undefined/vazio
        if (!sessionId || sessionId === 'null' || sessionId === 'undefined') {
            sessionId = 'session_fallback_' + Date.now();
            sessionStorage.setItem('clone_tracker_session', sessionId);
        }

        return sessionId;
    }

    // Captura informações do navegador
    function getBrowserInfo() {
        const sessionId = getSessionId(); // Pega o session ID

        console.log('[Clone Tracker] Session ID:', sessionId); // DEBUG

        return {
            sessionId: sessionId, // SEMPRE será uma string válida
            domain: window.location.hostname || 'unknown',
            url: window.location.href || '',
            referrer: document.referrer || '',
            screenResolution: (screen.width || 0) + 'x' + (screen.height || 0),
            language: navigator.language || navigator.userLanguage || 'unknown',
            timestamp: new Date().toISOString(),
            requests: []
        };
    }

    let capturedRequests = [];
    const originalFetch = window.fetch;

    // Intercepta Fetch API
    window.fetch = function(...args) {
        const url = typeof args[0] === 'string' ? args[0] : args[0]?.url || '';
        const method = args[1]?.method || 'GET';

        // Não interceptar chamadas para o próprio collector
        if (url.includes('/api/collect')) {
            return originalFetch.apply(this, args);
        }

        const requestData = {
            type: 'fetch',
            url: url,
            method: method,
            timestamp: new Date().toISOString()
        };

        capturedRequests.push(requestData);

        return originalFetch.apply(this, args).then(response => {
            const responseData = {
                type: 'fetch_response',
                url: url,
                status: response.status,
                timestamp: new Date().toISOString()
            };
            capturedRequests.push(responseData);
            return response;
        }).catch(err => {
            return Promise.reject(err);
        });
    };

    // Captura cliques
    document.addEventListener('click', function(e) {
        if (!e.target) return;

        const clickData = {
            type: 'click',
            tagName: e.target.tagName || '',
            id: e.target.id || '',
            className: e.target.className || '',
            text: (e.target.innerText || '').substring(0, 100),
            timestamp: new Date().toISOString()
        };
        capturedRequests.push(clickData);
    }, true);

    // Envia os dados para o servidor
    function sendToServer() {
        if (capturedRequests.length === 0) {
            console.log('[Clone Tracker] Nada para enviar');
            return;
        }

        const data = getBrowserInfo();
        data.requests = [...capturedRequests];

        // VALIDAÇÃO CRÍTICA: Garantir que sessionId existe
        if (!data.sessionId) {
            console.error('[Clone Tracker] ERRO: sessionId está vazio!');
            data.sessionId = 'session_error_' + Date.now();
        }

        console.log('[Clone Tracker] Enviando dados:', {
            sessionId: data.sessionId,
            requests: data.requests.length
        });

        // Limpa o array
        capturedRequests = [];

        // Envia via fetch original (não interceptado)
        originalFetch(COLLECTOR_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
            mode: 'cors'
        })
        .then(response => {
            console.log('[Clone Tracker] ✅ Resposta:', response.status);
            return response.json();
        })
        .then(result => {
            console.log('[Clone Tracker] ✅ Sucesso:', result);
        })
        .catch(error => {
            console.error('[Clone Tracker] ❌ Erro:', error);
        });
    }

    // Envia dados a cada 10 segundos
    setInterval(sendToServer, 10000);

    // Envia dados quando a página é fechada
    window.addEventListener('beforeunload', function() {
        if (capturedRequests.length > 0) {
            const data = getBrowserInfo();
            data.requests = [...capturedRequests];

            if (navigator.sendBeacon) {
                navigator.sendBeacon(COLLECTOR_URL, JSON.stringify(data));
            }
        }
    });

    // Captura evento inicial
    capturedRequests.push({
        type: 'page_load',
        url: window.location.href,
        timestamp: new Date().toISOString()
    });

    // Envia após 2 segundos
    setTimeout(sendToServer, 2000);

    console.log('[Clone Tracker] ✅ Initialized - Session:', getSessionId());
})();
</script>
```

---

## 📝 CHECKLIST DE CORREÇÃO

Execute na ordem:

- [ ] **1. SSH no servidor**
  ```bash
  ssh forge@cc-sorteador.on-forge.com
  ```

- [ ] **2. Ir para o diretório do projeto**
  ```bash
  cd /home/forge/cc-sorteador.on-forge.com
  ```

- [ ] **3. Executar migrations**
  ```bash
  php artisan migrate --force
  ```

- [ ] **4. Limpar cache**
  ```bash
  php artisan config:clear && php artisan cache:clear
  ```

- [ ] **5. Atualizar script no GTM**
  - Copiar o script ATUALIZADO acima
  - Colar no GTM substituindo o antigo
  - Publicar

- [ ] **6. Testar no site clonado**
  - Abrir Console (F12)
  - Procurar mensagem: `[Clone Tracker] ✅ Initialized - Session: session_...`
  - Aguardar 2 segundos
  - Deve aparecer: `[Clone Tracker] ✅ Sucesso: {status: "success", id: ...}`

---

## 🧪 TESTAR APÓS CORREÇÃO

### Teste 1: Verificar tabela sessions

```bash
ssh forge@cc-sorteador.on-forge.com
cd /home/forge/cc-sorteador.on-forge.com
php artisan tinker
```

Dentro do tinker:
```php
DB::table('sessions')->count();
// Deve retornar 0 ou mais (não deve dar erro)
```

### Teste 2: Testar endpoint

```bash
curl -X POST https://cc-sorteador.on-forge.com/api/collect \
  -H "Content-Type: application/json" \
  -d '{
    "sessionId": "test_correcao_123",
    "domain": "test.com",
    "url": "https://test.com",
    "referrer": "",
    "screenResolution": "1920x1080",
    "language": "pt-BR",
    "timestamp": "2025-12-15T12:00:00.000Z",
    "requests": []
  }'
```

Deve retornar:
```json
{"status":"success","id":2}
```

---

## ⚠️ SE AINDA DER ERRO

### Verificar logs em tempo real

```bash
ssh forge@cc-sorteador.on-forge.com
tail -f /home/forge/cc-sorteador.on-forge.com/storage/logs/laravel.log
```

Deixe rodando e faça um teste do site clonado. Veja o erro exato.

---

## 📞 COMANDOS ÚTEIS

### Ver última migration executada
```bash
php artisan migrate:status
```

### Criar tabela sessions manualmente (se necessário)
```bash
php artisan session:table
php artisan migrate --force
```

### Ver estrutura do banco
```bash
php artisan tinker
```
Dentro do tinker:
```php
DB::select('SHOW TABLES');
DB::select('DESCRIBE clone_logs');
```

---

**Execute esses comandos e me diga o resultado!** 🚀
