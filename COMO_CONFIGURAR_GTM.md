# 🎯 Como Configurar Corretamente no GTM

## ❌ Problema: Script funciona no Console mas não no GTM

### Causa Principal:
O GTM executa o script em um **timing/contexto diferente** do Console.

---

## ✅ SOLUÇÃO COMPLETA

### Passo 1: Teste de Debug PRIMEIRO

1. Use o arquivo **GTM_DEBUG.html**
2. Cole no GTM como HTML Personalizado
3. Acionador: **All Pages**
4. Publicar
5. Abra o Console do site clonado
6. Procure mensagens `[GTM Debug]`
7. **Me envie** o que aparecer

---

### Passo 2: Configurar Script Forçado

Use o arquivo **GTM_FORCADO.html**

#### No Google Tag Manager:

1. **Tags** → **Nova Tag**

2. **Nome da Tag:**
   ```
   Clone Tracker - Forçado
   ```

3. **Tipo de Tag:**
   - Clique em "Configuração da tag"
   - Escolha: **HTML Personalizado**

4. **Cole o código** do arquivo `GTM_FORCADO.html`

5. **Configurações Avançadas** (MUITO IMPORTANTE!)

   Clique em "Configurações avançadas":

   - **Opção de disparar:**
     - ✅ Marque: "Once per event"
     - ✅ Marque: "Once per page"

   - **Prioridade da tag:**
     - Digite: `999`
     - (Isso faz executar ANTES de outras tags)

6. **Acionador** (CRÍTICO!)

   Clique em "Acionamento"

   **IMPORTANTE:** NÃO use "Page View"!

   Use um destes:

   **Opção A - Window Loaded (RECOMENDADO):**
   - Clique no **+** para criar novo acionador
   - Nome: `Window Loaded - All Pages`
   - Tipo de acionador: **Window Loaded**
   - Dispara em: **All Window Loaded Events**
   - Salvar

   **Opção B - DOM Ready:**
   - Nome: `DOM Ready - All Pages`
   - Tipo: **DOM Ready**
   - Dispara em: **All DOM Ready Events**
   - Salvar

   **Opção C - Timer (mais agressivo):**
   - Nome: `Timer - 1 segundo`
   - Tipo: **Timer**
   - Intervalo: `1000` (ms)
   - Limite: `1` (executa uma vez)
   - Dispara em: **All Timer Events**

7. **Salvar a Tag**

8. **Visualizar** (botão azul no canto superior direito)

9. **Testar no site clonado**

   Abra o Console e procure:
   ```
   [Clone Tracker] Inicializando...
   [Clone Tracker] ✅ Fetch interceptado
   [Clone Tracker] ✅ XHR interceptado
   [Clone Tracker] ✅ Inicializado
   ```

10. **Se funcionar:** Clique em "Enviar" para publicar

---

## 🔍 Diagnóstico de Problemas

### Problema 1: Nada aparece no Console

**Causa:** Tag não está disparando

**Solução:**
1. No modo Visualização do GTM
2. Verifique se a tag aparece em "Tags Fired"
3. Se não aparecer, mude o acionador para "Timer"

### Problema 2: Aparece "Inicializado" mas não captura requisições

**Causa:** Script executou DEPOIS das requisições

**Solução:**
1. Aumente a prioridade para `999`
2. Mude acionador para "DOM Ready" ou "Timer"

### Problema 3: "já inicializado anteriormente"

**Causa:** GTM executou a tag múltiplas vezes

**Solução:** Isso é normal! Significa que está funcionando.

### Problema 4: "sessionStorage não acessível"

**Causa:** Contexto do GTM bloqueado

**Solução:** O script tem fallback automático

---

## 🧪 Teste Rápido

Após configurar no GTM:

1. Abra o site clonado
2. Abra o Console (F12)
3. Cole:
   ```javascript
   console.log('Teste:', window.__CLONE_TRACKER__);
   ```

4. Deve aparecer:
   ```javascript
   {
     initialized: true,
     requests: [...],
     sessionId: "session_..."
   }
   ```

5. Faça algumas ações:
   - Clique em botões
   - Digite em campos
   - Envie formulários

6. Execute:
   ```javascript
   console.log('Total capturado:', window.__CLONE_TRACKER__.requests.length);
   ```

---

## 📊 Diferença: Console vs GTM

| Aspecto | Console | GTM |
|---------|---------|-----|
| **Quando executa** | Imediatamente | Após evento (Page View, Timer, etc) |
| **Contexto** | Window principal | Pode ser iframe/isolado |
| **Timing** | Antes de tudo | Depois de outras bibliotecas |
| **Acesso** | Direto ao window | Pode ter restrições |

Por isso o script precisa:
- ✅ Usar namespace global (`window.__CLONE_TRACKER__`)
- ✅ Salvar referências originais
- ✅ Executar no timing certo (acionador correto)
- ✅ Ter prioridade alta (999)

---

## 🎯 Checklist Final

Antes de publicar, verifique:

- [ ] Arquivo usado: `GTM_FORCADO.html`
- [ ] Tipo de tag: HTML Personalizado
- [ ] Acionador: **Window Loaded** ou **DOM Ready** (NÃO Page View)
- [ ] Prioridade: `999`
- [ ] Opção: "Once per page" marcado
- [ ] Testado no modo Visualização
- [ ] Console mostra `[Clone Tracker] ✅ Inicializado`
- [ ] Teste de captura funcionou

---

## 💡 Dica Extra

Se AINDA não funcionar, use esta configuração ULTRA AGRESSIVA:

**Acionador:**
- Tipo: **Personalizado - JavaScript Error**
- OU: **Timer** com intervalo de 500ms

Isso força o script a executar muito cedo!

---

## 📞 Próximos Passos

1. Execute o **GTM_DEBUG.html** PRIMEIRO
2. Me envie os logs do Console
3. Baseado nisso, vou ajustar o script final

**Teste agora e me diga o resultado!** 🚀
