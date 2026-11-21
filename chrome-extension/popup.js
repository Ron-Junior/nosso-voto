(() => {
  const DEFAULT_BASE_URL = 'http://localhost:8000';
  const baseUrlInput = document.getElementById('base_url');
  const apiTokenInput = document.getElementById('api_token');
  const categorySelect = document.getElementById('default_category');
  const form = document.getElementById('settings-form');
  const clipTabBtn = document.getElementById('clip-current-tab');
  const statusEl = document.getElementById('status');

  function setStatus(message, isError = false) {
    statusEl.textContent = message;
    statusEl.classList.toggle('is-error', isError);
  }

  function storageArea() {
    if (typeof chrome !== 'undefined' && chrome.storage) {
      return chrome.storage.sync || chrome.storage.local;
    }
    return null;
  }

  function loadSettings() {
    const area = storageArea();
    if (!area) {
      baseUrlInput.value = DEFAULT_BASE_URL;
      return;
    }

    area.get(['apiToken', 'baseUrl', 'defaultCategory'], (result) => {
      baseUrlInput.value = result.baseUrl || DEFAULT_BASE_URL;
      apiTokenInput.value = result.apiToken || '';
      categorySelect.value = result.defaultCategory || 'decoracao';
    });
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const baseUrl = baseUrlInput.value.trim().replace(/\/+$/, '');
    const apiToken = apiTokenInput.value.trim();
    const defaultCategory = categorySelect.value;

    const area = storageArea();
    if (!area) {
      setStatus('Storage da extensão indisponível.', true);
      return;
    }

    area.set({ baseUrl, apiToken, defaultCategory }, () => {
      setStatus('Token e preferências salvos com sucesso!');
    });
  });

  clipTabBtn.addEventListener('click', () => {
    const baseUrl = baseUrlInput.value.trim().replace(/\/+$/, '');
    const apiToken = apiTokenInput.value.trim();
    const category = categorySelect.value;

    if (!apiToken) {
      setStatus('Informe e salve seu api_token primeiro.', true);
      return;
    }

    if (typeof chrome === 'undefined' || !chrome.tabs) {
      setStatus('API de abas do Chrome indisponível.', true);
      return;
    }

    chrome.tabs.query({ active: true, currentWindow: true }, async (tabs) => {
      const activeTab = tabs && tabs[0];
      if (!activeTab || !activeTab.url || !/pinterest\.com/i.test(activeTab.url)) {
        setStatus('Abra um Pin ou Pasta no pinterest.com para salvar.', true);
        return;
      }

      try {
        const type = /\/pin\//i.test(activeTab.url) ? 'pin' : 'board';
        const response = await fetch(`${baseUrl}/api/v1/inspirations/clip`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${apiToken}`,
            'X-Api-Token': apiToken,
          },
          body: JSON.stringify({
            category,
            type,
            title: (activeTab.title || '').replace(/\s*\|\s*Pinterest.*$/i, '').trim(),
            pinterest_url: activeTab.url,
          }),
        });

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
          throw new Error(payload.message || `Erro HTTP ${response.status}`);
        }

        setStatus('Salvo no Nosso Voto com sucesso!');
      } catch (error) {
        setStatus(error.message, true);
      }
    });
  });

  loadSettings();
})();

