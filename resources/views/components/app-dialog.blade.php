<style>
  .app-dialog-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: grid;
    place-items: center;
    padding: 20px;
    background: rgba(5, 3, 8, 0.82);
  }
  .app-dialog-overlay[hidden] { display: none; }
  .app-dialog {
    width: min(440px, 100%);
    padding: 24px;
    color: #fff;
    background: var(--blue-dark, #0e1530);
    border: 2px solid var(--gold, #f0c030);
    box-shadow: 6px 6px 0 var(--gold-dim, #7a6000), 0 0 32px rgba(240,192,48,0.2);
  }
  .app-dialog-title {
    margin-bottom: 12px;
    padding-bottom: 10px;
    color: var(--gold, #f0c030);
    border-bottom: 1px solid var(--blue-mid, #1e2a50);
    font: 700 15px 'Cinzel', serif;
    letter-spacing: 0.06em;
  }
  .app-dialog-message {
    color: var(--text-muted, rgba(255,255,255,0.82));
    font-size: 14px;
    line-height: 1.55;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
  }
  .app-dialog-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  }
  .app-dialog-button {
    min-height: 38px;
    padding: 8px 14px;
    border: 2px solid var(--gold, #f0c030);
    font: 700 11px 'Outfit', sans-serif;
    letter-spacing: 0.06em;
    cursor: pointer;
  }
  .app-dialog-cancel {
    color: var(--gold, #f0c030);
    background: var(--blue-mid, #1e2a50);
  }
  .app-dialog-confirm {
    color: var(--blue-deep, #090d1e);
    background: var(--gold, #f0c030);
  }
  .app-dialog-confirm.is-danger {
    color: #fff;
    background: var(--red, #c0392b);
    border-color: var(--red, #c0392b);
  }
  .app-dialog-button:focus-visible {
    outline: 3px solid #fff;
    outline-offset: 3px;
  }
</style>

<div class="app-dialog-overlay" id="appDialogOverlay" hidden>
  <section class="app-dialog" role="alertdialog" aria-modal="true" aria-labelledby="appDialogTitle" aria-describedby="appDialogMessage" tabindex="-1">
    <h2 class="app-dialog-title" id="appDialogTitle">NOTICE</h2>
    <p class="app-dialog-message" id="appDialogMessage"></p>
    <div class="app-dialog-actions">
      <button type="button" class="app-dialog-button app-dialog-cancel" id="appDialogCancel" hidden>CANCEL</button>
      <button type="button" class="app-dialog-button app-dialog-confirm" id="appDialogConfirm">OK</button>
    </div>
  </section>
</div>

<script>
  (() => {
    const overlay = document.getElementById('appDialogOverlay');
    const title = document.getElementById('appDialogTitle');
    const message = document.getElementById('appDialogMessage');
    const cancelButton = document.getElementById('appDialogCancel');
    const confirmButton = document.getElementById('appDialogConfirm');
    const pendingDialogs = [];
    let activeDialog = null;

    function finishDialog(result) {
      if (!activeDialog) return;
      const { resolve, previousFocus } = activeDialog;
      activeDialog = null;
      overlay.hidden = true;
      resolve(result);
      if (previousFocus && previousFocus.isConnected) previousFocus.focus();
      openNextDialog();
    }

    function openNextDialog() {
      if (activeDialog || pendingDialogs.length === 0) return;
      activeDialog = pendingDialogs.shift();
      title.textContent = activeDialog.options.title || (activeDialog.options.confirm ? 'CONFIRM ACTION' : 'NOTICE');
      message.textContent = String(activeDialog.message ?? '');
      cancelButton.hidden = !activeDialog.options.confirm;
      cancelButton.textContent = activeDialog.options.cancelLabel || 'CANCEL';
      confirmButton.textContent = activeDialog.options.confirmLabel || (activeDialog.options.confirm ? 'CONFIRM' : 'OK');
      confirmButton.classList.toggle('is-danger', activeDialog.options.danger === true);
      overlay.hidden = false;
      (activeDialog.options.confirm ? cancelButton : confirmButton).focus();
    }

    window.showAppDialog = (dialogMessage, options = {}) => new Promise(resolve => {
      pendingDialogs.push({
        message: dialogMessage,
        options,
        resolve,
        previousFocus: document.activeElement
      });
      openNextDialog();
    });

    cancelButton.addEventListener('click', () => finishDialog(false));
    confirmButton.addEventListener('click', () => finishDialog(true));
    overlay.addEventListener('click', event => {
      if (event.target === overlay) finishDialog(!activeDialog?.options.confirm);
    });
    document.addEventListener('keydown', event => {
      if (!activeDialog) return;
      if (event.key === 'Escape') {
        event.preventDefault();
        finishDialog(!activeDialog.options.confirm);
      } else if (event.key === 'Tab') {
        event.preventDefault();
        const focusableButtons = activeDialog.options.confirm
          ? [cancelButton, confirmButton]
          : [confirmButton];
        const currentIndex = focusableButtons.indexOf(document.activeElement);
        const direction = event.shiftKey ? -1 : 1;
        const nextIndex = (currentIndex + direction + focusableButtons.length) % focusableButtons.length;
        focusableButtons[nextIndex].focus();
      }
    });
  })();
</script>
