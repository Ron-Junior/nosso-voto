(() => {
  const DEFAULT_BASE_URL = 'http://localhost:8000';
  const STORAGE_KEYS = ['apiToken', 'baseUrl', 'defaultCategory'];
  const BUTTON_CLASS = 'nv-pinterest-save-btn';

  function getSettings() {
    return new Promise((resolve) => {
      if (typeof chrome === 'undefined' || !chrome.storage) {
        resolve({ apiToken: '', baseUrl: DEFAULT_BASE_URL, defaultCategory: 'decoracao' });
        return;
      }

      const area = chrome.storage.sync || chrome.storage.local;
      area.get(STORAGE_KEYS, (result) => {
        resolve({
          apiToken: (result.apiToken || '').trim(),
          baseUrl: (result.baseUrl || DEFAULT_BASE_URL).trim().replace(/\/+$/, ''),
          defaultCategory: result.defaultCategory || 'decoracao',
        });
      });
    });
  }

  function extractPinMetadata(container) {
    const linkEl = container.matches('a[href*="/pin/"]')
      ? container
      : container.querySelector('a[href*="/pin/"]');

    const rawHref = linkEl ? linkEl.getAttribute('href') : window.location.pathname;
    const pinterestUrl = rawHref && rawHref.startsWith('http')
      ? rawHref
      : new URL(rawHref || window.location.href, window.location.origin).toString();

    const imgEl = container.querySelector('img[src]');
    const imageUrl = imgEl ? imgEl.src : null;
    const rawTitle = (imgEl && imgEl.alt) || (linkEl && linkEl.getAttribute('aria-label')) || document.title || '';

    return {
      pinterest_url: pinterestUrl,
      image_url: imageUrl,
      title: rawTitle.replace(/\s*\|\s*Pinterest.*$/i, '').trim(),
      type: /\/pin\//i.test(pinterestUrl) ? 'pin' : 'board',
    };
  }

  async function sendClipToNossoVoto(payload) {
    const settings = await getSettings();

    if (!settings.apiToken) {
      throw new Error('Configure seu api_token no popup da extensão antes de salvar.');
    }

    const endpoint = `${settings.baseUrl}/api/v1/inspirations/clip`;

    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${settings.apiToken}`,
        'X-Api-Token': settings.apiToken,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.message || `Erro ao salvar (HTTP ${response.status}).`);
    }

    return data;
  }

  function removeExistingPopover() {
    const existing = document.getElementById('nv-clip-popover');
    if (existing) {
      existing.remove();
    }
  }

  async function openClipPopover(pinData, sourceButton) {
    removeExistingPopover();
    const settings = await getSettings();

    let selectedCategory = settings.defaultCategory || 'decoracao';

    const popover = document.createElement('div');
    popover.id = 'nv-clip-popover';
    popover.className = 'nv-clip-popover';

    popover.innerHTML = `
      <div class="nv-clip-popover-header">
        <p class="nv-clip-popover-title">Salvar no Nosso Voto</p>
        <button type="button" class="nv-clip-close" aria-label="Fechar">×</button>
      </div>
      <label class="nv-clip-label">Especialidade</label>
      <div class="nv-clip-categories">
        <button type="button" class="nv-clip-cat-btn" data-category="decoracao">Decoração</button>
        <button type="button" class="nv-clip-cat-btn" data-category="maquiagem">Maquiagem</button>
        <button type="button" class="nv-clip-cat-btn" data-category="penteado">Penteado</button>
      </div>
      <label class="nv-clip-label" for="nv-clip-title">Título</label>
      <input id="nv-clip-title" class="nv-clip-input" type="text" placeholder="Título da referência">
      <label class="nv-clip-label" for="nv-clip-notes">Nota da noiva (opcional)</label>
      <textarea id="nv-clip-notes" class="nv-clip-textarea" rows="2" placeholder="Ex.: Referência de paleta ou detalhe..."></textarea>
      <button type="button" class="nv-clip-submit">Salvar Inspiração</button>
      <div class="nv-clip-status" id="nv-clip-status"></div>
    `;

    document.body.appendChild(popover);

    const titleInput = popover.querySelector('#nv-clip-title');
    const notesInput = popover.querySelector('#nv-clip-notes');
    const statusEl = popover.querySelector('#nv-clip-status');
    const submitBtn = popover.querySelector('.nv-clip-submit');
    const closeBtn = popover.querySelector('.nv-clip-close');
    const catButtons = Array.from(popover.querySelectorAll('.nv-clip-cat-btn'));

    titleInput.value = pinData.title || '';

    const updateActiveCategory = () => {
      catButtons.forEach((btn) => {
        btn.classList.toggle('is-active', btn.dataset.category === selectedCategory);
      });
    };

    updateActiveCategory();

    catButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        selectedCategory = btn.dataset.category;
        updateActiveCategory();
      });
    });

    closeBtn.addEventListener('click', removeExistingPopover);

    submitBtn.addEventListener('click', async () => {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Salvando...';
      statusEl.className = 'nv-clip-status';
      statusEl.textContent = '';

      try {
        await sendClipToNossoVoto({
          category: selectedCategory,
          type: pinData.type,
          title: titleInput.value.trim(),
          pinterest_url: pinData.pinterest_url,
          image_url: pinData.image_url,
          bride_notes: notesInput.value.trim(),
        });

        statusEl.className = 'nv-clip-status is-success';
        statusEl.textContent = 'Inspiração salva no seu moodboard!';

        if (sourceButton) {
          sourceButton.classList.add('is-saved');
          sourceButton.textContent = 'Salvo no Nosso Voto';
        }

        setTimeout(removeExistingPopover, 1400);
      } catch (error) {
        statusEl.className = 'nv-clip-status is-error';
        statusEl.textContent = error.message;
      } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Salvar Inspiração';
      }
    });
  }

  function injectButtonIntoPin(pinEl) {
    if (!pinEl || pinEl.dataset.nvButtonInjected === 'true') {
      return;
    }

    const link = pinEl.querySelector('a[href*="/pin/"]');
    const img = pinEl.querySelector('img');
    if (!link && !img) {
      return;
    }

    pinEl.dataset.nvButtonInjected = 'true';
    pinEl.classList.add('nv-pin-host');

    const button = document.createElement('button');
    button.type = 'button';
    button.className = BUTTON_CLASS;
    button.textContent = 'Salvar no Nosso Voto';

    button.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      const metadata = extractPinMetadata(pinEl);
      openClipPopover(metadata, button);
    });

    pinEl.appendChild(button);
  }

  function scanAndInjectPins() {
    const pinSelectors = [
      '[data-test-id="pin"]',
      '[data-test-id="pinWrapper"]',
      'div[data-grid-item]',
    ];

    document.querySelectorAll(pinSelectors.join(',')).forEach(injectButtonIntoPin);

    if (/\/pin\//i.test(window.location.pathname)) {
      const closeup = document.querySelector('[data-test-id="closeup-image"], [data-test-id="pin-closeup-image"]');
      if (closeup && closeup.parentElement) {
        injectButtonIntoPin(closeup.parentElement);
      }
    }
  }

  scanAndInjectPins();

  const observer = new MutationObserver(() => {
    scanAndInjectPins();
  });

  if (document.body) {
    observer.observe(document.body, { childList: true, subtree: true });
  }
})();

