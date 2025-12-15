# Guia de Instalação do Clone Tracker via Google Tag Manager (GTM)

Este guia explica como configurar o rastreamento do site clonador usando Google Tag Manager.

## Pré-requisitos

1. Acesso ao Google Tag Manager do site alvo
2. O projeto Laravel rodando e acessível via HTTPS
3. URL do endpoint de coleta: `https://seu-dominio.com/api/collect`

## Passo 1: Configurar CORS no Laravel

Antes de configurar o GTM, você precisa permitir requisições cross-origin no seu servidor Laravel.

### 1.1 Instalar pacote CORS (se ainda não estiver instalado)

```bash
composer require fruitcake/laravel-cors
```

### 1.2 Publicar configuração

```bash
php artisan config:publish cors
```

### 1.3 Configurar CORS em `config/cors.php`

```php
return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'], // Em produção, especifique os domínios permitidos
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

## Passo 2: Preparar o Script

### 2.1 Editar o arquivo `public/gtm-tracker.js`

Abra o arquivo e altere a URL do coletor na linha 5:

```javascript
const COLLECTOR_URL = 'https://SEU-DOMINIO.com/api/collect';
```

Substitua `SEU-DOMINIO.com` pelo domínio onde seu projeto Laravel está hospedado.

### 2.2 Hospedar o Script (Opcional)

Você tem duas opções:

**Opção A:** Hospedar no seu próprio servidor Laravel
- O arquivo já está em `public/gtm-tracker.js`
- Acessível via: `https://seu-dominio.com/gtm-tracker.js`

**Opção B:** Usar o código inline no GTM (recomendado)
- Copie todo o conteúdo do arquivo `gtm-tracker.js`
- Cole diretamente no GTM (explicado abaixo)

## Passo 3: Configurar o Google Tag Manager

### 3.1 Acessar o GTM do site alvo

1. Faça login no Google Tag Manager
2. Selecione o container do site que você quer rastrear

### 3.2 Criar uma Nova Tag

1. No menu lateral, clique em **Tags**
2. Clique em **Nova**
3. Dê um nome descritivo: `Clone Tracker - Full Capture`

### 3.3 Configurar a Tag

1. Clique em **Configuração da tag**
2. Escolha **HTML Personalizado**
3. Cole o seguinte código:

```html
<script>
// Cole aqui o conteúdo completo do arquivo gtm-tracker.js
// Certifique-se de alterar a URL do COLLECTOR_URL
</script>
```

4. Marque as opções:
   - ✅ **Suporte a document.write**
   - ✅ **Executar tag uma vez por página**

### 3.4 Configurar o Acionamento (Trigger)

1. Clique em **Acionamento**
2. Clique no ícone **+** no canto superior direito
3. Dê um nome: `All Pages - Page Load`
4. Clique em **Configuração do acionador**
5. Escolha **Visualização de página**
6. Selecione **Todas as visualizações de página**
7. Salve o acionador

### 3.5 Salvar a Tag

1. Clique em **Salvar** no canto superior direito

## Passo 4: Testar a Configuração

### 4.1 Modo de Visualização do GTM

1. No GTM, clique em **Visualizar** no canto superior direito
2. Acesse o site alvo em uma nova aba
3. Verifique no painel do GTM se a tag `Clone Tracker - Full Capture` foi disparada
4. Abra o Console do navegador (F12)
5. Procure pela mensagem: `[Clone Tracker] Initialized - Session: session_...`

### 4.2 Verificar no Dashboard

1. Acesse o dashboard do Clone Catcher: `https://seu-dominio.com`
2. Faça login com as credenciais configuradas
3. Verifique se os logs estão sendo recebidos

### 4.3 Testar Captura de Dados

No site alvo, execute as seguintes ações:

- ✅ Navegue entre páginas
- ✅ Clique em botões e links
- ✅ Preencha formulários
- ✅ Faça requisições AJAX/Fetch
- ✅ Feche e abra a página

Cada ação deve ser capturada e enviada para o servidor.

## Passo 5: Publicar no GTM

Após confirmar que tudo está funcionando:

1. Clique em **Enviar** no canto superior direito do GTM
2. Dê um nome à versão: `Clone Tracker v1.0`
3. Adicione uma descrição explicando as mudanças
4. Clique em **Publicar**

## O que é Capturado

O script captura as seguintes informações:

### Informações do Navegador
- Session ID único
- Domínio e URL atual
- Referrer (de onde o usuário veio)
- Resolução da tela
- Idioma do navegador
- Timestamp de cada evento

### Eventos Capturados
- **Page Load**: Carregamento de páginas
- **Clicks**: Cliques em elementos (botões, links, etc.)
- **Form Submit**: Submissão de formulários (senhas são mascaradas)
- **Input Changes**: Mudanças em campos de texto (senhas são mascaradas)
- **Fetch/XHR**: Todas as requisições HTTP (APIs, AJAX, etc.)
- **Fetch/XHR Responses**: Respostas das requisições HTTP

### Proteção de Dados Sensíveis

O script mascara automaticamente:
- Campos de senha
- Campos com "cvv" no nome
- Campos com "card" no nome
- Qualquer campo com "senha" ou "password" no nome

Esses campos aparecem como `[MASKED]` nos logs.

## Frequência de Envio

Os dados são enviados para o servidor em três momentos:

1. **A cada 10 segundos**: Envia dados coletados automaticamente
2. **2 segundos após o carregamento**: Envia dados iniciais da página
3. **Ao fechar a página**: Envia dados pendentes usando `sendBeacon`

## Solução de Problemas

### A tag não dispara

- Verifique se o acionador está configurado para "Todas as visualizações de página"
- Confirme que a tag está publicada, não apenas em modo de visualização

### Erro de CORS

```
Access to fetch at 'https://seu-dominio.com/api/collect' from origin 'https://site-alvo.com'
has been blocked by CORS policy
```

**Solução**: Configure o CORS corretamente no Laravel (Passo 1)

### Dados não aparecem no dashboard

1. Verifique se o endpoint está correto no script
2. Confirme que o banco de dados está configurado
3. Execute as migrations: `php artisan migrate`
4. Verifique os logs do Laravel: `storage/logs/laravel.log`

### Session ID muda a cada página

- O Session ID é armazenado no `sessionStorage`
- É normal mudar entre diferentes abas ou após limpar o cache
- Cada sessão de navegação terá um ID único

## Monitoramento em Tempo Real

Para monitorar em tempo real:

```bash
# No servidor Laravel
tail -f storage/logs/laravel.log | grep "Clone activity detected"
```

## Segurança e Ética

⚠️ **IMPORTANTE**: Este sistema deve ser usado apenas:

- Em sites que você possui ou tem autorização explícita para monitorar
- Para fins de segurança e proteção contra clonagem
- Em conformidade com leis de privacidade (LGPD, GDPR, etc.)

**Não use este sistema para:**
- Rastrear usuários sem consentimento
- Coletar dados pessoais sensíveis sem autorização
- Violar a privacidade de terceiros

## Suporte

Se você encontrar problemas:

1. Verifique os logs do navegador (Console F12)
2. Verifique os logs do Laravel (`storage/logs/laravel.log`)
3. Teste o endpoint manualmente com `curl` ou Postman
4. Verifique se o CORS está configurado corretamente

## Exemplo de Teste com cURL

```bash
curl -X POST https://seu-dominio.com/api/collect \
  -H "Content-Type: application/json" \
  -d '{
    "sessionId": "test_session_123",
    "domain": "example.com",
    "url": "https://example.com/test",
    "referrer": "",
    "screenResolution": "1920x1080",
    "language": "pt-BR",
    "timestamp": "2025-12-15T10:00:00.000Z",
    "requests": [
      {
        "type": "page_load",
        "url": "https://example.com/test",
        "timestamp": "2025-12-15T10:00:00.000Z"
      }
    ]
  }'
```

Resposta esperada:
```json
{
  "status": "success",
  "id": 1
}
```

## Próximos Passos

Após a configuração:

1. Monitore o dashboard regularmente
2. Analise padrões de acesso suspeitos
3. Configure alertas para atividades anormais
4. Faça backup regular dos dados coletados

---

**Versão**: 1.0
**Data**: 15/12/2025
**Projeto**: Clone Catcher
