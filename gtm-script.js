// Clone Catcher - GTM Script
// Este script deve ser adicionado como uma Tag HTML Personalizada no Google Tag Manager
// Acionador: All Pages

(function() {
  // CONFIGURAÇÃO: Adicione seus domínios autorizados aqui
  var allowedDomains = ['seu-dominio-original.com', 'www.seu-dominio-original.com'];
  var currentDomain = window.location.hostname;

  // Se não estiver em um domínio autorizado, ativar coleta
  if (allowedDomains.indexOf(currentDomain) === -1) {

    // CONFIGURAÇÃO: URL do seu servidor Clone Catcher
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

    // ==========================================
    // INTERCEPTAR FETCH
    // ==========================================
    var originalFetch = window.fetch;
    window.fetch = function() {
      var url = arguments[0];
      var options = arguments[1] || {};

      var requestData = {
        type: 'fetch',
        method: options.method || 'GET',
        url: typeof url === 'string' ? url : url.url,
        headers: options.headers || {},
        body: options.body || null,
        timestamp: new Date().toISOString()
      };

      if (requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
        sendToCollector(requestData);
      }

      return originalFetch.apply(this, arguments)
        .then(function(response) {
          var responseClone = response.clone();

          responseClone.text().then(function(body) {
            var responseData = {
              type: 'fetch_response',
              url: requestData.url,
              status: response.status,
              statusText: response.statusText,
              headers: {},
              bodyPreview: body.substring(0, 500),
              timestamp: new Date().toISOString()
            };

            response.headers.forEach(function(value, key) {
              responseData.headers[key] = value;
            });

            if (requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
              sendToCollector(responseData);
            }
          }).catch(function(){});

          return response;
        });
    };

    // ==========================================
    // INTERCEPTAR XMLHttpRequest
    // ==========================================
    var originalXHROpen = XMLHttpRequest.prototype.open;
    var originalXHRSend = XMLHttpRequest.prototype.send;
    var originalXHRSetRequestHeader = XMLHttpRequest.prototype.setRequestHeader;

    XMLHttpRequest.prototype.open = function(method, url) {
      this._requestData = {
        type: 'xhr',
        method: method,
        url: url,
        headers: {},
        timestamp: new Date().toISOString()
      };
      return originalXHROpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.setRequestHeader = function(header, value) {
      if (this._requestData) {
        this._requestData.headers[header] = value;
      }
      return originalXHRSetRequestHeader.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function(body) {
      if (this._requestData) {
        this._requestData.body = body;

        if (this._requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
          sendToCollector(this._requestData);
        }

        var xhr = this;
        var originalOnReadyStateChange = xhr.onreadystatechange;

        xhr.onreadystatechange = function() {
          if (xhr.readyState === 4) {
            var responseData = {
              type: 'xhr_response',
              url: xhr._requestData.url,
              status: xhr.status,
              statusText: xhr.statusText,
              responseHeaders: xhr.getAllResponseHeaders(),
              responsePreview: xhr.responseText ? xhr.responseText.substring(0, 500) : '',
              timestamp: new Date().toISOString()
            };

            if (xhr._requestData.url.indexOf(COLLECTOR_ENDPOINT) === -1) {
              sendToCollector(responseData);
            }
          }

          if (originalOnReadyStateChange) {
            originalOnReadyStateChange.apply(this, arguments);
          }
        };
      }

      return originalXHRSend.apply(this, arguments);
    };

    // ==========================================
    // CAPTURAR EVENTOS IMPORTANTES
    // ==========================================

    // Cliques em links
    document.addEventListener('click', function(e) {
      var target = e.target.closest('a');
      if (target && target.href) {
        sendToCollector({
          type: 'click',
          element: 'link',
          href: target.href,
          text: target.textContent.substring(0, 100),
          timestamp: new Date().toISOString()
        });
      }
    }, true);

    // Submissão de formulários
    document.addEventListener('submit', function(e) {
      var form = e.target;
      var formData = {
        type: 'form_submit',
        action: form.action,
        method: form.method,
        fields: {},
        timestamp: new Date().toISOString()
      };

      var inputs = form.querySelectorAll('input, select, textarea');
      inputs.forEach(function(input) {
        formData.fields[input.name || input.id] = {
          type: input.type,
          required: input.required
        };
      });

      sendToCollector(formData);
    }, true);

    // Erros JavaScript
    window.addEventListener('error', function(e) {
      sendToCollector({
        type: 'javascript_error',
        message: e.message,
        filename: e.filename,
        lineno: e.lineno,
        colno: e.colno,
        timestamp: new Date().toISOString()
      });
    });

    // Capturar informações iniciais
    sendToCollector({
      type: 'page_load',
      url: window.location.href,
      title: document.title,
      cookies: document.cookie ? 'present' : 'none',
      localStorage: typeof(Storage) !== 'undefined' ? 'available' : 'unavailable',
      timestamp: new Date().toISOString()
    });

    // Capturar recursos carregados
    if (window.performance && window.performance.getEntriesByType) {
      setTimeout(function() {
        var resources = window.performance.getEntriesByType('resource');
        sendToCollector({
          type: 'resources_loaded',
          count: resources.length,
          resources: resources.slice(0, 50).map(function(r) {
            return {
              name: r.name,
              type: r.initiatorType,
              duration: r.duration,
              size: r.transferSize
            };
          }),
          timestamp: new Date().toISOString()
        });
      }, 5000);
    }
  }
})();
