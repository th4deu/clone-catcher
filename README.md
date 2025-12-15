# 🔍 Clone Catcher

Sistema de monitoramento e rastreamento de clones não autorizados de sites.

## Sobre

Clone Catcher é uma aplicação Laravel que permite monitorar e coletar informações sobre sites que clonaram seu conteúdo e mantiveram seu Google Tag Manager (GTM) no código.

## Recursos

- ✅ **Coleta de Dados**: Captura todas as requisições HTTP, cliques, formulários e eventos
- ✅ **Dashboard Visual**: Interface moderna com gráficos e estatísticas
- ✅ **Análise por Domínio**: Visualize atividade específica por domínio clonado
- ✅ **Export de Dados**: Exporte todos os logs em formato JSON
- ✅ **API RESTful**: Endpoint para receber dados do GTM
- ✅ **CORS Habilitado**: Aceita requisições de qualquer origem

## Instalação

### Pré-requisitos

- PHP 8.2+
- Composer
- MySQL/PostgreSQL/SQLite

### Setup no Laravel Forge

1. **Crie um novo site no Forge**
   - Server: Escolha seu servidor
   - Root Domain: `clone-catcher.seudominio.com`
   - Web Directory: `/public`

2. **Clone o repositório**
   ```bash
   cd /home/forge/clone-catcher.seudominio.com
   git init
   git remote add origin <seu-repo-git>
   git pull origin main
   ```

3. **Configure o .env**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edite o `.env`:
   ```env
   APP_NAME="Clone Catcher"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://clone-catcher.seudominio.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=clone_catcher
   DB_USERNAME=forge
   DB_PASSWORD=sua-senha
   ```

4. **Instale as dependências**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

5. **Execute as migrations**
   ```bash
   php artisan migrate --force
   ```

6. **Configure permissões**
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

7. **Configure SSL no Forge**
   - Vá em SSL → Let's Encrypt
   - Obtenha certificado SSL gratuito

## Configuração do GTM

### 1. Acesse seu Google Tag Manager

### 2. Crie uma Nova Tag

- **Tipo**: Tag HTML Personalizada
- **Nome**: "Clone Catcher"
- **Acionador**: All Pages

### 3. Cole o Script

```javascript
<script>
(function() {
  var allowedDomains = ['seu-dominio-original.com', 'www.seu-dominio-original.com'];
  var currentDomain = window.location.hostname;

  if (allowedDomains.indexOf(currentDomain) === -1) {
    var COLLECTOR_ENDPOINT = 'https://clone-catcher.seudominio.com/api/collect';

    var requestBuffer = [];
    var sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

    function sendToCollector(data) {
      requestBuffer.push(data);
      if (requestBuffer.length >= 5) {
        flushBuffer();
      }
    }

    function flushBuffer() {
      if (requestBuffer.length === 0) return;

      var payload = {
        sessionId: sessionId,
        domain: currentDomain,
        url: window.location.href,
        timestamp: new Date().toISOString(),
        userAgent: navigator.userAgent,
        referrer: document.referrer,
        screenResolution: screen.width + 'x' + screen.height,
        language: navigator.language,
        requests: requestBuffer.slice()
      };

      var originalFetch = window.fetch;
      originalFetch(COLLECTOR_ENDPOINT, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload),
        mode: 'no-cors'
      }).catch(function(){});

      requestBuffer = [];
    }

    setInterval(flushBuffer, 10000);
    window.addEventListener('beforeunload', flushBuffer);

    // Interceptar Fetch
    var originalFetch = window.fetch;
    window.fetch = function() {
      var url = arguments[0];
      var options = arguments[1] || {};

      var requestData = {
        type: 'fetch',
        method: options.method || 'GET',
        url: typeof url === 'string' ? url : url.url,
        headers: options.headers || {},
        timestamp: new Date().toISOString()
      };

      if (requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
        sendToCollector(requestData);
      }

      return originalFetch.apply(this, arguments);
    };

    // Interceptar XMLHttpRequest
    var originalXHROpen = XMLHttpRequest.prototype.open;
    var originalXHRSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function(method, url) {
      this._requestData = {
        type: 'xhr',
        method: method,
        url: url,
        timestamp: new Date().toISOString()
      };
      return originalXHROpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function(body) {
      if (this._requestData && this._requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
        sendToCollector(this._requestData);
      }
      return originalXHRSend.apply(this, arguments);
    };

    // Capturar eventos
    sendToCollector({
      type: 'page_load',
      url: window.location.href,
      title: document.title,
      timestamp: new Date().toISOString()
    });
  }
})();
</script>
```

### 4. IMPORTANTE: Atualize as Variáveis

No script acima, **substitua**:
- `seu-dominio-original.com` → Seu domínio real
- `https://clone-catcher.seudominio.com` → URL do seu Clone Catcher

### 5. Publique a Tag

- Clique em "Submit" no GTM
- Publique a versão

## Uso

### Acessar o Dashboard

Acesse: `https://clone-catcher.seudominio.com`

Você verá:
- Total de logs coletados
- Sessões únicas
- Domínios clonados detectados
- IPs únicos
- Gráfico de atividade diária
- Lista de domínios clonados
- Atividade recente

### Ver Detalhes de um Domínio

Clique em "View Details" ao lado de qualquer domínio para ver:
- Todas as sessões desse domínio
- IPs que acessaram
- Requisições capturadas
- Histórico completo

### Ver Detalhes de um Log

Clique em qualquer log para ver:
- Informações completas da sessão
- Todas as requisições HTTP capturadas
- User Agent
- JSON completo dos dados

### Exportar Dados

Clique em "Export Data" no menu superior para baixar todos os logs em JSON.

## API Endpoints

### POST /api/collect

Recebe dados coletados do GTM.

**Request:**
```json
{
  "sessionId": "session_123456_abc",
  "domain": "clone-malicioso.com",
  "url": "https://clone-malicioso.com/pagina",
  "timestamp": "2025-12-15T10:30:00.000Z",
  "userAgent": "Mozilla/5.0...",
  "referrer": "https://google.com",
  "screenResolution": "1920x1080",
  "language": "pt-BR",
  "requests": [
    {
      "type": "fetch",
      "method": "GET",
      "url": "https://api.example.com/data",
      "timestamp": "2025-12-15T10:30:01.000Z"
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "id": 123
}
```

### GET /api/stats

Retorna estatísticas gerais.

**Response:**
```json
{
  "stats": {
    "total_logs": 150,
    "total_sessions": 45,
    "total_domains": 3,
    "total_ips": 38
  },
  "domains": [
    {
      "domain": "clone1.com",
      "count": 80
    }
  ]
}
```

## Estrutura do Banco de Dados

### Tabela: clone_logs

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único |
| session_id | string | ID da sessão |
| domain | string | Domínio clonado |
| url | string | URL completa |
| client_ip | string | IP do visitante |
| client_user_agent | string | User Agent |
| referrer | string | URL de origem |
| screen_resolution | string | Resolução da tela |
| language | string | Idioma do navegador |
| requests | json | Array de requisições capturadas |
| client_timestamp | timestamp | Timestamp do cliente |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

## Segurança

- O sistema coleta apenas dados técnicos e de navegação
- Não coleta dados pessoais ou senhas
- CORS configurado para aceitar qualquer origem (necessário para o funcionamento)
- Todos os dados são armazenados de forma segura no banco de dados

## Deploy Automático (Opcional)

Configure um deploy automático no Forge:

1. Vá em "Apps" → "Git Repository"
2. Conecte seu repositório
3. Configure "Quick Deploy"
4. Adicione no script de deploy:

```bash
cd /home/forge/clone-catcher.seudominio.com
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Troubleshooting

### Dados não estão sendo coletados

1. Verifique se o GTM está publicado
2. Confirme a URL do endpoint no script GTM
3. Verifique os logs do Laravel: `storage/logs/laravel.log`
4. Teste o endpoint manualmente com curl:

```bash
curl -X POST https://clone-catcher.seudominio.com/api/collect \
  -H "Content-Type: application/json" \
  -d '{"sessionId":"test","domain":"test.com","url":"https://test.com"}'
```

### Erro de CORS

Se houver erro de CORS, verifique:
- `config/cors.php` deve ter `'allowed_origins' => ['*']`
- Execute: `php artisan config:cache`

### Dashboard em branco

1. Verifique permissões: `chmod -R 755 storage`
2. Limpe cache: `php artisan cache:clear`
3. Verifique logs: `tail -f storage/logs/laravel.log`

## Licença

Este projeto é de código aberto para fins de proteção de propriedade intelectual.

## Suporte

Para questões ou problemas, abra uma issue no repositório.
