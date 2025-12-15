# 🔴 GTM NÃO EXECUTA - Solução Passo a Passo

## Teste 1: Script Super Simples

Vamos confirmar se o GTM está executando ALGUMA COISA.

### 📝 Passo a Passo EXATO:

#### 1. Acesse o Google Tag Manager
```
https://tagmanager.google.com
```

#### 2. Selecione o Container
- Clique no container/workspace do site clonador
- Certifique-se que está no container CORRETO

#### 3. Criar Nova Tag

1. No menu lateral, clique em **"Tags"**
2. Clique no botão azul **"Nova"** (canto superior direito)

#### 4. Configurar a Tag

**Nome da Tag:**
```
TESTE SIMPLES
```

**Configuração da Tag:**
1. Clique na caixa "Configuração da tag" (em branco)
2. No menu que abre, role até o fim
3. Clique em **"HTML Personalizado"**
4. Cole EXATAMENTE este código:

```html
<script>
alert('GTM FUNCIONOU!');
console.log('GTM FUNCIONOU!');
</script>
```

**Acionamento:**
1. Clique na caixa "Acionamento" (em branco)
2. Clique no **"+"** no canto superior direito
3. Nome do acionador: `Todas as Páginas`
4. Clique em "Configuração do acionador"
5. Escolha **"Visualização de página"**
6. Selecione **"Todas as visualizações de página"**
7. Clique em **"Salvar"**

#### 5. Salvar a Tag
1. Clique em **"Salvar"** no canto superior direito
2. A tag foi criada (mas ainda NÃO publicada)

#### 6. Testar no Modo Visualização

1. Clique em **"Visualizar"** no canto superior direito
2. Uma nova janela deve abrir com um campo de URL
3. Digite a URL do site clonador
4. Clique em **"Connect"** ou **"Conectar"**
5. O site deve abrir em uma nova aba
6. Na parte inferior do site, deve aparecer um painel do GTM

#### 7. Verificar se a Tag Disparou

No painel do GTM (parte inferior):
- Procure pela tag **"TESTE SIMPLES"**
- Deve estar na coluna **"Tags Fired"** (Tags Disparadas)
- Se estiver em **"Tags Not Fired"**, a tag NÃO executou

#### 8. Verificar o Resultado

Se a tag disparou:
- ✅ Deve aparecer um **ALERTA** dizendo "GTM FUNCIONOU!"
- ✅ No Console (F12) deve ter a mensagem "GTM FUNCIONOU!"

---

## 🔍 Diagnóstico

### ✅ SE O ALERTA APARECEU:

**Significa:** O GTM está funcionando!

**Próximo passo:** Use o arquivo `GTM_FORCADO.html` no lugar do teste simples

### ❌ SE O ALERTA NÃO APARECEU:

Verifique:

#### A. A tag está na coluna "Tags Fired"?

**SIM:** A tag executou, mas o script tem erro
- Abra o Console (F12)
- Procure por erros em vermelho
- Me envie o erro

**NÃO:** A tag não executou

Possíveis causas:

**1. Acionador errado:**
- Volte na tag
- Verifique se o acionador é "Visualização de página - Todas as visualizações"

**2. Tag pausada/desabilitada:**
- Verifique se a tag tem um ícone de "pause" ou está cinza
- Se sim, clique com botão direito → "Enable"

**3. Container não publicado:**
- Você precisa estar no modo **"Visualizar"**
- OU publicar o container

**4. Filtros/Exceções:**
- Verifique se não há exceções configuradas na tag

**5. GTM não instalado no site:**
- Veja o código fonte do site (Ctrl+U)
- Procure por `googletagmanager.com/gtm.js`
- Se não encontrar, o GTM NÃO está instalado

---

## 📸 Checklist Visual

Quando você abre o modo Visualização:

```
┌─────────────────────────────────────┐
│  Site (aba normal)                  │
│                                     │
│  [Conteúdo do site aqui]           │
│                                     │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  GTM Debug Panel (parte inferior)   │
│  ┌───────────┬──────────────────┐  │
│  │ Tags Fired│ Tags Not Fired   │  │
│  │ TESTE     │                  │  │ ← Deve estar aqui
│  │ SIMPLES   │                  │  │
│  └───────────┴──────────────────┘  │
└─────────────────────────────────────┘
```

---

## 🚨 Erros Comuns

### Erro 1: "Preview mode is not available"
**Solução:**
- Seu navegador está bloqueando cookies de terceiros
- Permita cookies para `tagmanager.google.com`

### Erro 2: Painel do GTM não abre
**Solução:**
- Desabilite extensões do navegador (AdBlock, etc)
- Use aba anônima
- Tente outro navegador

### Erro 3: "Container not found"
**Solução:**
- O GTM não está instalado no site
- Ou você está no container errado

---

## ✅ Próximos Passos

1. **FAÇA O TESTE SIMPLES PRIMEIRO**
2. **Me diga:**
   - ❓ O alerta apareceu?
   - ❓ A tag está em "Tags Fired"?
   - ❓ Tem algum erro no Console?

3. **Com base na resposta**, vou ajustar a solução

---

## 📞 Me envie estas informações:

```
1. O alerta "GTM FUNCIONOU!" apareceu? [ ] SIM [ ] NÃO

2. A tag aparece em "Tags Fired"? [ ] SIM [ ] NÃO

3. No Console (F12), aparece algum erro? [ ] SIM [ ] NÃO
   Se SIM, qual erro?

4. Quando você vê o código fonte (Ctrl+U),
   tem "googletagmanager.com" no código? [ ] SIM [ ] NÃO
```

**Faça o teste e me envie essas respostas!** 🎯
