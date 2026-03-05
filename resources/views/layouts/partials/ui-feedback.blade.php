<div id="global-ui-loader"
     class="nmis-loader is-visible"
     role="status"
     aria-live="polite"
     aria-label="Loading">
    <div class="nmis-loader__panel">
        <div class="nmis-loader__brand">
            <img src="{{ asset('images/nexus-logo.png') }}" alt="NexusFlow" class="nmis-loader__logo">
            <div>
                <p class="nmis-loader__title">{{ config('app.company_name', config('app.name', 'NMIS')) }}</p>
                <p id="global-ui-loader-message" class="nmis-loader__message">Loading workspace...</p>
            </div>
        </div>
        <div class="nmis-loader__progress">
            <span class="nmis-loader__bar"></span>
        </div>
    </div>
</div>

<div id="global-confirm-modal"
     class="nmis-confirm hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="global-confirm-title">
    <div class="nmis-confirm__backdrop" data-confirm-dismiss="true"></div>
    <div class="nmis-confirm__panel">
        <h3 id="global-confirm-title" class="nmis-confirm__title">Confirm Action</h3>
        <p id="global-confirm-message" class="nmis-confirm__message">Are you sure you want to continue?</p>
        <div class="nmis-confirm__actions">
            <button type="button" id="global-confirm-cancel" class="nmis-confirm__btn nmis-confirm__btn--ghost">Cancel</button>
            <button type="button" id="global-confirm-ok" class="nmis-confirm__btn nmis-confirm__btn--danger">Yes, Continue</button>
        </div>
    </div>
</div>

<style>
    .nmis-loader {
        position: fixed;
        inset: 0;
        z-index: 120;
        display: none;
        align-items: center;
        justify-content: center;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(4px);
    }

    .nmis-loader.is-visible {
        display: flex;
    }

    .nmis-loader__panel {
        width: min(28rem, calc(100vw - 2rem));
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: #ffffff;
        box-shadow: 0 24px 45px rgba(15, 23, 42, 0.22);
        padding: 1rem 1.1rem;
    }

    .nmis-loader__brand {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .nmis-loader__logo {
        width: 2.5rem;
        height: 2.5rem;
        object-fit: contain;
        border-radius: 0.75rem;
        border: 1px solid #dbeafe;
        background: #f8fafc;
        padding: 0.35rem;
    }

    .nmis-loader__title {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #0f172a;
    }

    .nmis-loader__message {
        margin: 0.15rem 0 0 0;
        font-size: 0.78rem;
        color: #475569;
    }

    .nmis-loader__progress {
        position: relative;
        overflow: hidden;
        margin-top: 0.95rem;
        height: 0.35rem;
        border-radius: 999px;
        background: #e2e8f0;
    }

    .nmis-loader__bar {
        position: absolute;
        inset: 0;
        width: 38%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--nmis-primary), var(--nmis-secondary));
        animation: nmis-loader-move 1.2s linear infinite;
    }

    @keyframes nmis-loader-move {
        0% { transform: translateX(-120%); }
        100% { transform: translateX(300%); }
    }

    .nmis-confirm {
        position: fixed;
        inset: 0;
        z-index: 130;
    }

    .nmis-confirm.hidden {
        display: none;
    }

    .nmis-confirm__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(2px);
    }

    .nmis-confirm__panel {
        position: relative;
        z-index: 1;
        width: min(30rem, calc(100vw - 2rem));
        margin: 12vh auto 0;
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: #ffffff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.28);
        padding: 1.1rem 1.1rem 0.95rem;
    }

    .nmis-confirm__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
    }

    .nmis-confirm__message {
        margin: 0.5rem 0 0;
        font-size: 0.88rem;
        color: #475569;
        line-height: 1.35;
    }

    .nmis-confirm__actions {
        margin-top: 1rem;
        display: flex;
        justify-content: flex-end;
        gap: 0.55rem;
    }

    .nmis-confirm__btn {
        border-radius: 0.6rem;
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.55rem 0.82rem;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .nmis-confirm__btn--ghost {
        background: #ffffff;
        border-color: #cbd5e1;
        color: #334155;
    }

    .nmis-confirm__btn--ghost:hover {
        background: #f8fafc;
    }

    .nmis-confirm__btn--danger {
        background: #dc2626;
        color: #ffffff;
    }

    .nmis-confirm__btn--danger:hover {
        background: #b91c1c;
    }
</style>

<script>
    (() => {
        if (window.NMISUiFeedbackInitialized) {
            return;
        }
        window.NMISUiFeedbackInitialized = true;

        const loader = document.getElementById('global-ui-loader');
        const loaderMessage = document.getElementById('global-ui-loader-message');
        const confirmModal = document.getElementById('global-confirm-modal');
        const confirmMessage = document.getElementById('global-confirm-message');
        const confirmTitle = document.getElementById('global-confirm-title');
        const confirmOk = document.getElementById('global-confirm-ok');
        const confirmCancel = document.getElementById('global-confirm-cancel');
        const initAt = Date.now();

        let pendingConfirm = null;

        const setBodyLock = (locked) => {
            if (locked) {
                document.body.style.overflow = 'hidden';
            } else if (!loader?.classList.contains('is-visible') && confirmModal?.classList.contains('hidden')) {
                document.body.style.overflow = '';
            }
        };

        const showLoader = (message = 'Processing request...') => {
            if (!loader) return;
            if (loaderMessage) loaderMessage.textContent = message;
            loader.classList.add('is-visible');
            setBodyLock(true);
        };

        const hideLoader = () => {
            if (!loader) return;
            loader.classList.remove('is-visible');
            setBodyLock(false);
        };

        const closeConfirm = () => {
            if (!confirmModal) return;
            confirmModal.classList.add('hidden');
            pendingConfirm = null;
            setBodyLock(false);
        };

        const openConfirm = ({ title, message, confirmText, onConfirm }) => {
            if (!confirmModal) {
                onConfirm();
                return;
            }
            confirmTitle.textContent = title || 'Confirm Action';
            confirmMessage.textContent = message || 'Are you sure you want to continue?';
            confirmOk.textContent = confirmText || 'Yes, Continue';
            pendingConfirm = onConfirm;
            confirmModal.classList.remove('hidden');
            setBodyLock(true);
        };

        const effectiveMethod = (form) => {
            const spoof = form.querySelector('input[name="_method"]');
            if (spoof?.value) return spoof.value.toUpperCase();
            return (form.method || 'GET').toUpperCase();
        };

        const needsConfirm = (form, submitter) => {
            if (form.dataset.noConfirm === 'true' || submitter?.dataset.noConfirm === 'true') return false;
            if (form.dataset.confirm || submitter?.dataset.confirm) return true;

            const method = effectiveMethod(form);
            if (method === 'DELETE') return true;

            const payload = `${form.action || ''} ${submitter?.textContent || ''} ${submitter?.className || ''}`.toLowerCase();
            const riskyTerms = [
                'delete', 'destroy', 'remove', 'discard', 'force-close',
                'close trip', 'close order', 'mark completed', 'complete transport',
                'terminate', 'deactivate', 'reject'
            ];

            return riskyTerms.some((term) => payload.includes(term));
        };

        const confirmMessageFor = (form, submitter) => {
            if (submitter?.dataset.confirmMessage) return submitter.dataset.confirmMessage;
            if (form.dataset.confirmMessage) return form.dataset.confirmMessage;

            const method = effectiveMethod(form);
            const payload = `${form.action || ''} ${submitter?.textContent || ''}`.toLowerCase();

            if (method === 'DELETE' || payload.includes('delete') || payload.includes('remove')) {
                return 'This action may permanently remove data. Continue?';
            }
            if (payload.includes('close')) {
                return 'This action will close the current workflow and may not be reversible. Continue?';
            }
            if (payload.includes('complete')) {
                return 'This action will mark the process as completed. Continue?';
            }

            return 'Please confirm you want to proceed with this action.';
        };

        document.addEventListener('submit', (event) => {
            const form = event.target instanceof HTMLFormElement ? event.target : null;
            if (!form) return;

            const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;

            if (form.dataset.confirming === 'true') {
                form.dataset.confirming = 'false';
            } else if (needsConfirm(form, submitter)) {
                event.preventDefault();
                openConfirm({
                    title: submitter?.dataset.confirmTitle || form.dataset.confirmTitle || 'Confirm Submission',
                    message: confirmMessageFor(form, submitter),
                    confirmText: submitter?.dataset.confirmButton || form.dataset.confirmButton || 'Yes, Continue',
                    onConfirm: () => {
                        form.dataset.confirming = 'true';
                        if (typeof form.requestSubmit === 'function') {
                            if (submitter instanceof HTMLElement) {
                                form.requestSubmit(submitter);
                            } else {
                                form.requestSubmit();
                            }
                        } else {
                            form.submit();
                        }
                    },
                });
                return;
            }

            if (form.dataset.noLoader !== 'true' && submitter?.dataset.noLoader !== 'true') {
                const msg = submitter?.dataset.loadingText || form.dataset.loadingText || 'Submitting data...';
                showLoader(msg);
            }
        }, true);

        document.addEventListener('click', (event) => {
            const target = event.target instanceof HTMLElement ? event.target : null;
            if (!target) return;

            const anchor = target.closest('a[href]');
            if (!anchor) return;
            if (anchor.dataset.noLoader === 'true') return;
            if (anchor.hasAttribute('download')) return;
            if ((anchor.target || '').toLowerCase() === '_blank') return;

            const rawHref = anchor.getAttribute('href') || '';
            if (!rawHref || rawHref.startsWith('#') || rawHref.startsWith('javascript:')) return;

            const url = new URL(anchor.href, window.location.href);
            if (url.origin !== window.location.origin) return;

            if (anchor.dataset.confirm || anchor.dataset.confirmMessage) {
                event.preventDefault();
                openConfirm({
                    title: anchor.dataset.confirmTitle || 'Confirm Navigation',
                    message: anchor.dataset.confirmMessage || 'Continue to the next page?',
                    confirmText: anchor.dataset.confirmButton || 'Continue',
                    onConfirm: () => {
                        showLoader(anchor.dataset.loadingText || 'Opening page...');
                        window.location.assign(anchor.href);
                    },
                });
                return;
            }

            showLoader(anchor.dataset.loadingText || 'Opening page...');
        }, true);

        confirmOk?.addEventListener('click', () => {
            const action = pendingConfirm;
            closeConfirm();
            if (typeof action === 'function') {
                action();
            }
        });

        confirmCancel?.addEventListener('click', closeConfirm);

        confirmModal?.addEventListener('click', (event) => {
            const element = event.target instanceof HTMLElement ? event.target : null;
            if (element?.dataset.confirmDismiss === 'true') {
                closeConfirm();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && confirmModal && !confirmModal.classList.contains('hidden')) {
                closeConfirm();
            }
        });

        const clearInitialLoader = () => {
            const minDisplayMs = 320;
            const wait = Math.max(0, minDisplayMs - (Date.now() - initAt));
            window.setTimeout(hideLoader, wait);
        };

        window.addEventListener('load', clearInitialLoader);
        window.addEventListener('beforeunload', () => {
            if (!loader?.classList.contains('is-visible')) {
                showLoader('Loading page...');
            }
        });
        window.addEventListener('pageshow', () => {
            hideLoader();
        });

        window.NMISUi = {
            showLoader,
            hideLoader,
            openConfirm,
        };
    })();
</script>
