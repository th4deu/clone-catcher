# ❌ GTM Não Funciona no Site Clonado - Como Resolver

## Diagnóstico Rápido

O teste com `curl` funcionou ✅, mas o GTM no site clonado não funciona ❌.

**Causa Provável:** Problema de CORS (Cross-Origin Resource Sharing)

## 🔍 Passo 1: Identificar o Problema

### Opção A: Usar arquivo de Debug (RECOMENDADO)

1. Acesse o site clonado
2. Abra o Console (F12)
3. Cole este código:

```javascript
// Criar e abrir arquivo de debug
var debugScript = document.createElement('script');
debugScript.src = 'https://cc-sorteador.on-forge.com/DEBUG_GTM.html';
document.body.appendChild(debugScript);
```

Ou simplesmente abra o arquivo `DEBUG_GTM.html` que foi criado.

### Opção B: Testar Direto no Console

1. Acesse o site clonado
2. Abra o Console (F12)
3. Cole e execute:

```javascript
// Teste rápido de CORS
fetch('https://cc-sorteador.on-forge.com/api/collect', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        sessionId: 'test_' + Date.now(),
        domain: window.location.hostname,
        url: window.location.href,
        referrer: '',
        screenResolution: '1920x1080',
        language: 'pt-BR',
        timestamp: new Date().toISOString(),
        requests: [{ type: 'test', timestamp: new Date().toISOString() }]
    })
})
.then(r => r.json())
.then(data => console.log('✅ SUCESSO:', data))
.catch(err => console.error('❌ ERRO:', err));
```

## 📊 Interpretando os Erros

### Erro 1: CORS Policy Error

```
Access to fetch at 'https://cc-sorteador.on-forge.com/api/collect' from origin 'https://site-clonado.com'
has been blocked by CORS policy: No 'Access-Control-Allow-Origin' header is present
```

**Solução:** Configure o CORS no servidor Laravel (veja Passo 2)

### Erro 2: Network Error

```
Failed to fetch
TypeError: Failed to fetch
```

**Possíveis causas:**
- CORS bloqueando
- Firewall bloqueando
- Certificado SSL inválido

**Solução:** Verifique o CORS e SSL do servidor

### Erro 3: GTM não dispara a tag

- Tag não aparece no modo Visualização do GTM
- Nenhuma requisição aparece na aba Network

**Solução:** Verifique a configuração da Tag no GTM (veja Passo 3)

## 🔧 Passo 2: Configurar CORS Corretamente

### 2.1 Verificar configuração atual

No servidor, verifique o arquivo `config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // ⬅️ Deve estar assim para aceitar qualquer origem
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

### 2.2 Se o CORS não estiver configurado

Execute no servidor:

```bash
# 1. Instalar pacote CORS (se não tiver)
composer require fruitcake/laravel-cors

# 2. Publicar configuração
php artisan vendor:publish --tag="cors"

# 3. Limpar cache
php artisan config:clear
php artisan cache:clear
```

### 2.3 Adicionar middleware CORS

Verifique se o middleware está registrado em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'basic.auth' => \App\Http\Middleware\BasicAuthMiddleware::class,
    ]);

    // Adicione esta linha se não existir
    $middleware->api(prepend: [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);
})
```

### 2.4 Alternativa: CORS Manual no Controller

Se preferir controlar o CORS manualmente, edite `app/Http/Controllers/Api/CollectorController.php`:

```php
public function store(Request $request)
{
    // Adicionar headers CORS manualmente
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    // Se for requisição OPTIONS (preflight)
    if ($request->method() === 'OPTIONS') {
        return response('', 200);
    }

    // Resto do código...
}
```

## 🏷️ Passo 3: Verificar Configuração do GTM

### 3.1 Checklist da Tag

- [ ] Tag criada como **HTML Personalizado**
- [ ] Script completo colado (incluindo `<script>` e `</script>`)
- [ ] URL do COLLECTOR_URL está correta: `https://cc-sorteador.on-forge.com/api/collect`
- [ ] Acionador configurado para **All Pages** (Todas as páginas)
- [ ] Tag está **Publicada** (não apenas em modo Visualização)

### 3.2 Configuração Correta no GTM

1. **Nome da Tag:** `Clone Tracker - Full Monitor`

2. **Tipo:** HTML Personalizado

3. **Código:** (copie do arquivo `GTM_SCRIPT_PRONTO.html`)

4. **Opções avançadas:**
   - ✅ Suporte a document.write
   - ✅ Executar tag uma vez por página
   - ✅ Executar tag uma vez por evento

5. **Acionador:**
   - Tipo: Visualização de página
   - Dispara em: Todas as visualizações de página
   - Nome: `All Pages`

### 3.3 Testar no Modo Visualização

1. No GTM, clique em **Visualizar**
2. Acesse o site clonado em outra aba
3. Volte para o painel do GTM
4. Verifique se a tag `Clone Tracker - Full Monitor` aparece em **Tags Fired**
5. Se não aparecer:
   - Verifique o acionador
   - Verifique se não há erros JavaScript no Console

## 🐛 Passo 4: Debug Avançado

### 4.1 Verificar se o script está sendo carregado

No Console do navegador (site clonado):

```javascript
// Deve aparecer a mensagem de inicialização
console.log('Procure por: [Clone Tracker] Initialized');
```

### 4.2 Verificar requisições na aba Network

1. Abra o Console (F12)
2. Vá para a aba **Network** (Rede)
3. Filtre por: `collect`
4. Recarregue a página
5. Você deve ver requisições para `https://cc-sorteador.on-forge.com/api/collect`

Se não aparecer nenhuma requisição:
- ❌ Script não está sendo executado
- Verifique a configuração da Tag no GTM

Se aparecer requisição em vermelho (Failed):
- ❌ CORS está bloqueando
- Configure o CORS no servidor (Passo 2)

### 4.3 Verificar Payload da Requisição

Na aba Network, clique na requisição `collect`:

1. **Headers:**
   - Request Method: `POST`
   - Content-Type: `application/json`

2. **Payload:**
   - Deve conter: sessionId, domain, url, requests, etc.

3. **Response:**
   - Status: `201 Created`
   - Body: `{"status":"success","id":123}`

### 4.4 Forçar envio manual

No Console do site clonado:

```javascript
// Forçar envio imediato
fetch('https://cc-sorteador.on-forge.com/api/collect', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    mode: 'cors',
    body: JSON.stringify({
        sessionId: 'manual_test_' + Date.now(),
        domain: window.location.hostname,
        url: window.location.href,
        referrer: document.referrer,
        screenResolution: screen.width + 'x' + screen.height,
        language: navigator.language,
        timestamp: new Date().toISOString(),
        requests: [{
            type: 'manual_test',
            message: 'Teste manual do console',
            timestamp: new Date().toISOString()
        }]
    })
})
.then(r => {
    console.log('Status:', r.status);
    return r.json();
})
.then(data => console.log('✅ Resposta:', data))
.catch(err => console.error('❌ Erro:', err));
```

## ✅ Checklist de Solução

Use este checklist para resolver o problema:

### No Servidor Laravel:

- [ ] CORS configurado em `config/cors.php`
- [ ] `allowed_origins` está como `['*']`
- [ ] Middleware CORS registrado em `bootstrap/app.php`
- [ ] Cache limpo: `php artisan config:clear`
- [ ] Servidor reiniciado (se necessário)

### No GTM:

- [ ] Tag criada como HTML Personalizado
- [ ] Script completo colado
- [ ] URL do endpoint está correta
- [ ] Acionador é "All Pages"
- [ ] Tag está publicada
- [ ] Testado no modo Visualização

### No Site Clonado:

- [ ] Console não mostra erros de CORS
- [ ] Aba Network mostra requisições para `/api/collect`
- [ ] Requisições retornam status 201
- [ ] Mensagem `[Clone Tracker] Initialized` aparece no Console

## 🔬 Teste Final

Execute este comando no Console do site clonado:

```javascript
(async function testEverything() {
    console.log('🔍 Iniciando diagnóstico completo...\n');

    // 1. Testar CORS
    console.log('1️⃣ Testando CORS...');
    try {
        const corsTest = await fetch('https://cc-sorteador.on-forge.com/api/collect', {
            method: 'OPTIONS'
        });
        console.log('✅ CORS OK - Status:', corsTest.status);
    } catch (err) {
        console.error('❌ CORS FALHOU:', err.message);
        return;
    }

    // 2. Testar envio de dados
    console.log('\n2️⃣ Testando envio de dados...');
    try {
        const response = await fetch('https://cc-sorteador.on-forge.com/api/collect', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                sessionId: 'diagnostic_' + Date.now(),
                domain: window.location.hostname,
                url: window.location.href,
                referrer: '',
                screenResolution: screen.width + 'x' + screen.height,
                language: navigator.language,
                timestamp: new Date().toISOString(),
                requests: [{ type: 'diagnostic', timestamp: new Date().toISOString() }]
            })
        });

        const data = await response.json();
        console.log('✅ ENVIO OK - Status:', response.status);
        console.log('✅ Resposta:', data);

        if (data.status === 'success') {
            console.log('\n🎉 TUDO FUNCIONANDO!');
            console.log('📊 ID do log:', data.id);
            console.log('\n✅ Você pode configurar o GTM agora!');
        }

    } catch (err) {
        console.error('❌ ENVIO FALHOU:', err.message);
    }
})();
```

Se este teste funcionar ✅, o problema está na configuração do GTM.
Se este teste falhar ❌, o problema está no CORS do servidor.

## 📞 Suporte

Se ainda não funcionar após seguir todos os passos:

1. **Verifique os logs do servidor:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Teste o endpoint diretamente:**
   ```bash
   curl -X POST https://cc-sorteador.on-forge.com/api/collect \
     -H "Content-Type: application/json" \
     -d '{"sessionId":"test","domain":"test.com","url":"https://test.com","referrer":"","screenResolution":"1920x1080","language":"pt-BR","timestamp":"2025-12-15T12:00:00.000Z","requests":[]}'
   ```

3. **Capture o erro exato:**
   - Abra o Console no site clonado
   - Tire um print do erro
   - Copie a mensagem de erro completa

---

**🎯 Próximo Passo:**

Use o arquivo `DEBUG_GTM.html` para fazer os testes no site clonado e identificar exatamente onde está o problema!
