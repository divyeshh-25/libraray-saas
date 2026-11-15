// Frontend-only interactions for the static admin UI. No network calls.
// jQuery + Select2 are loaded from a CDN (see layouts/app.blade.php), not npm.

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];

/* ---------- Select2 (searchable dropdowns with a "Select <field>" placeholder) ---------- */
const measureCtx = document.createElement('canvas').getContext('2d');
const widestOptionWidth = (select) => {
    measureCtx.font = window.getComputedStyle(select).font || '13px sans-serif';
    const widths = [...select.options].map((o) => measureCtx.measureText(o.text).width);
    return Math.max(...widths, 0) + 48; // + horizontal padding and the dropdown arrow
};

const initSelect2 = (root = document) => {
    if (!window.jQuery || !window.jQuery.fn.select2) return;
    window.jQuery('select[data-placeholder]', root).each(function () {
        if (window.jQuery(this).data('select2')) return;
        const isFilter = this.hasAttribute('data-table-filter');
        window.jQuery(this).select2({
            width: isFilter ? `${widestOptionWidth(this)}px` : '100%',
            placeholder: window.jQuery(this).data('placeholder'),
            allowClear: isFilter,
            minimumResultsForSearch: 2,
        });
    });
};
window.jQuery ? window.jQuery(() => initSelect2()) : initSelect2();

/* ---------- Cascading selects: narrow Shift/Fee plan options to the chosen branch ---------- */
const wireBranchCascades = (root = document) => {
    $$('[data-cascade-by-branch]', root).forEach((select) => {
        if (select.dataset.cascadeWired) return;
        select.dataset.cascadeWired = '1';

        const form = select.closest('form');
        const branchSelect = form && $('#branch_id', form);
        if (!branchSelect) return;

        const options = $$('option[data-branch-id]', select);
        const apply = () => {
            const branchId = branchSelect.value;
            let selectedStillValid = !select.value;
            options.forEach((opt) => {
                const matches = !branchId || opt.dataset.branchId === branchId;
                opt.hidden = !matches;
                opt.disabled = !matches || opt.dataset.unavailable === '1';
                if (matches && !opt.disabled && opt.value === select.value) selectedStillValid = true;
            });

            const hasSelect2 = window.jQuery && window.jQuery(select).data('select2');
            if (!selectedStillValid) {
                select.value = '';
                if (hasSelect2) window.jQuery(select).val('').trigger('change');
            }
            if (hasSelect2) {
                window.jQuery(select).select2('destroy');
                initSelect2(form);
            }
        };

        branchSelect.addEventListener('change', apply);
        if (window.jQuery) window.jQuery(branchSelect).on('change select2:select select2:clear select2:unselect', apply);
        apply();
    });
};
window.jQuery ? window.jQuery(() => wireBranchCascades()) : wireBranchCascades();

/* ---------- Auto-submit selects (e.g. attendance's branch/shift filters) ---------- */
const wireAutosubmit = (root = document) => {
    $$('[data-autosubmit]', root).forEach((el) => {
        if (el.dataset.autosubmitWired) return;
        el.dataset.autosubmitWired = '1';
        const submit = () => el.form?.submit();
        el.addEventListener('change', submit);
        if (window.jQuery) window.jQuery(el).on('change', submit);
    });
};
window.jQuery ? window.jQuery(() => wireAutosubmit()) : wireAutosubmit();

/* ---------- Sidebar (mobile) + collapsible groups ---------- */
const sidebar = $('#sidebar');
const overlay = $('#sidebar-overlay');
const setSidebar = (open) => {
    sidebar?.classList.toggle('-translate-x-full', !open);
    overlay?.classList.toggle('hidden', !open);
};
$$('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => setSidebar(true)));
$$('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => setSidebar(false)));

$$('[data-collapse]').forEach((g) => {
    $('[data-collapse-toggle]', g).addEventListener('click', () => {
        const body = $('[data-collapse-body]', g);
        body.classList.toggle('open');
        $('[data-chevron]', g).classList.toggle('rotate-90', body.classList.contains('open'));
    });
});

/* ---------- Offcanvas (ajax add/edit panels, Bootstrap-offcanvas style) ---------- */
$$('[data-offcanvas]').forEach((oc) => {
    const panel = $('[data-offcanvas-panel]', oc);
    const backdrop = $('[data-offcanvas-backdrop]', oc);
    const body = $('[data-offcanvas-body]', oc);
    const titleEl = $('[data-offcanvas-title]', oc);
    const csrf = $('meta[name="csrf-token"]')?.content;
    const tableId = oc.dataset.offcanvasTable;

    const refreshTable = () => {
        const table = tableId && $(`[data-table="${tableId}"]`);
        if (!table) return;
        fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((res) => res.text())
            .then((html) => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newTbody = doc.querySelector(`[data-table="${tableId}"] tbody`);
                const tbody = $('tbody', table);
                if (newTbody && tbody) tbody.innerHTML = newTbody.innerHTML;
                table.dispatchEvent(new Event('refresh'));
            })
            .catch(() => location.reload());
    };

    const open = () => {
        oc.classList.remove('hidden');
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
            backdrop.classList.remove('opacity-0');
        });
    };
    const close = () => {
        panel.classList.add('translate-x-full');
        backdrop.classList.add('opacity-0');
        setTimeout(() => { oc.classList.add('hidden'); if (body) body.innerHTML = ''; }, 200);
    };

    const loadForm = (url, title) => {
        if (titleEl && title) titleEl.textContent = title;
        if (body) body.innerHTML = '<div class="p-5 space-y-3"><span class="skeleton block" style="width:40%"></span><span class="skeleton block" style="width:100%;height:36px"></span><span class="skeleton block" style="width:100%;height:36px"></span></div>';
        open();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then((res) => res.text())
            .then((html) => { if (body) { body.innerHTML = html; initSelect2(body); wireBranchCascades(body); } })
            .catch(() => { if (body) body.innerHTML = '<div class="p-5 text-red-600 text-[13px]">Could not load the form. Please try again.</div>'; });
    };

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-offcanvas-url]');
        if (!trigger) return;
        e.preventDefault();
        loadForm(trigger.dataset.offcanvasUrl, trigger.dataset.offcanvasTitle);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('[data-offcanvas-close]')) return;
        e.preventDefault();
        close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !oc.classList.contains('hidden')) close();
    });

    document.addEventListener('submit', (e) => {
        const form = e.target.closest('[data-ajax-form]');
        if (!form || !body || !body.contains(form)) return;
        e.preventDefault();

        const submitBtn = $('button[type=submit]', form);
        const errorBox = $('[data-form-errors]', form);
        if (submitBtn) submitBtn.disabled = true;
        if (errorBox) errorBox.innerHTML = '';

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            body: new FormData(form),
        })
            .then(async (res) => {
                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    const messages = Object.values(data.errors || {}).flat();
                    if (errorBox) {
                        errorBox.innerHTML = `<div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]"><ul class="list-disc pl-4 space-y-0.5">${messages.map((m) => `<li>${m}</li>`).join('')}</ul></div>`;
                    }
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                if (!res.ok) {
                    toast(data.message || 'Something went wrong. Please try again.');
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }

                close();
                toast(data.message || 'Saved.');
                refreshTable();
            })
            .catch(() => {
                toast('Something went wrong. Please try again.');
                if (submitBtn) submitBtn.disabled = false;
            });
    });
});

/* ---------- Row action menus ---------- */
const closeMenus = () => $$('.menu.open').forEach((m) => m.classList.remove('open'));
document.addEventListener('click', (e) => {
    const toggle = e.target.closest('[data-menu-toggle]');
    const wasOpen = toggle && $('.menu', toggle.parentElement).classList.contains('open');
    closeMenus();
    if (toggle && !wasOpen) {
        const menu = $('.menu', toggle.parentElement);
        menu.classList.add('open');
        // Row menus live inside a scrolling table: position against the viewport so they are never clipped.
        if (toggle.closest('td')) {
            const r = toggle.getBoundingClientRect();
            const h = menu.offsetHeight;
            const up = r.bottom + h + 12 > window.innerHeight;
            Object.assign(menu.style, { position: 'fixed', left: 'auto', right: `${window.innerWidth - r.right}px`, top: `${up ? r.top - h - 4 : r.bottom + 4}px`, marginTop: '0' });
            menu.style.transformOrigin = up ? 'bottom right' : 'top right';
        }
    }
});
window.addEventListener('scroll', closeMenus, true);
window.addEventListener('resize', closeMenus);

/* ---------- Toast ---------- */
let toastTimer;
const toast = (msg) => {
    const t = $('#toast');
    $('[data-toast-text]', t).textContent = msg;
    t.classList.remove('hidden', 'show');
    t.classList.add('flex');
    void t.offsetWidth; // restart the entrance animation
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { t.classList.add('hidden'); t.classList.remove('flex', 'show'); }, 2600);
};
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-toast]');
    if (t) toast(t.dataset.toast);
});

/* ---------- Collect fee (centered modal, not an offcanvas) ---------- */
const feeModal = $('#collect-fee-modal');
window.__feeModalWired = !!feeModal;
if (feeModal) {
    const feeForm = $('form', feeModal);
    const csrf = $('meta[name="csrf-token"]')?.content;
    const closeFeeModal = () => { feeModal.classList.add('hidden'); feeModal.classList.remove('flex'); };

    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-collect-fee]');
        if (!trigger) return;
        e.preventDefault();
        feeForm.action = trigger.dataset.url;
        $('[name=fee_structure_id]', feeForm).value = trigger.dataset.feeStructureId || '';
        $('[name=amount]', feeForm).value = trigger.dataset.amount || '';
        $('#collect-fee-student-name', feeModal).textContent = trigger.dataset.studentName || '';
        $('[data-form-errors]', feeForm).innerHTML = '';
        feeModal.classList.remove('hidden');
        feeModal.classList.add('flex');
    });

    $$('[data-modal-close]', feeModal).forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); closeFeeModal(); }));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !feeModal.classList.contains('hidden')) closeFeeModal(); });

    feeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = $('button[type=submit]', feeForm);
        const errorBox = $('[data-form-errors]', feeForm);
        if (submitBtn) submitBtn.disabled = true;
        errorBox.innerHTML = '';

        try {
            const res = await fetch(feeForm.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                body: new FormData(feeForm),
            });
            const data = await res.json().catch(() => ({}));

            if (res.status === 422) {
                const messages = Object.values(data.errors || {}).flat();
                errorBox.innerHTML = `<div class="rounded-md bg-red-50 text-red-700 px-3.5 py-3 text-[13px]"><ul class="list-disc pl-4 space-y-0.5">${messages.map((m) => `<li>${m}</li>`).join('')}</ul></div>`;
                return;
            }
            if (!res.ok) { toast(data.message || 'Something went wrong. Please try again.'); return; }

            closeFeeModal();
            toast(data.message || 'Fee collected.');
            setTimeout(() => location.reload(), 300);
        } catch {
            toast('Something went wrong. Please try again.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });
}

/* ---------- Confirm via SweetAlert2 before submitting a form ---------- */
document.addEventListener('submit', (e) => {
    const form = e.target.closest('[data-confirm-submit]');
    if (!form || form.dataset.confirmed || !window.Swal) return;
    e.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: form.dataset.confirmSubmit,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, continue',
        confirmButtonColor: '#dc2626',
    }).then((result) => {
        if (result.isConfirmed) {
            form.dataset.confirmed = '1';
            form.submit();
        }
    });
});

/* ---------- Restore (undo soft-delete) ---------- */
document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-restore-url]');
    if (!b) return;
    const url = b.dataset.restoreUrl;
    const token = $('meta[name="csrf-token"]')?.content;
    const row = b.closest('tr, [data-removable]');
    b.disabled = true;
    fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' } })
        .then(async (res) => {
            const body = await res.json().catch(() => ({}));
            if (!res.ok) { toast(body.message || 'Could not restore. Please try again.'); b.disabled = false; return; }
            if (row) {
                const table = row.closest('[data-table]');
                row.remove();
                if (table) table.dispatchEvent(new Event('refresh'));
            }
            toast(body.message || 'Restored.');
            if (b.dataset.redirect) setTimeout(() => { location.href = b.dataset.redirect; }, 500);
        })
        .catch(() => { toast('Could not restore. Please try again.'); b.disabled = false; });
});

/* ---------- Confirm dialog (removes the row on confirm) ---------- */
const modal = $('#confirm-modal');
let pendingRow = null;
let pendingDeleteUrl = null;
const closeModal = () => { if (!modal) return; modal.classList.add('hidden'); modal.classList.remove('flex'); pendingRow = null; pendingDeleteUrl = null; };
document.addEventListener('click', (e) => {
    const c = e.target.closest('[data-confirm]');
    if (!c) return;
    $('[data-confirm-title]').textContent = c.dataset.confirm;
    pendingRow = c.closest('tr, [data-removable]');
    pendingDeleteUrl = c.dataset.deleteUrl || null;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
});
$('[data-confirm-cancel]')?.addEventListener('click', closeModal);
modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
$('[data-confirm-ok]')?.addEventListener('click', () => {
    const row = pendingRow;
    const url = pendingDeleteUrl;
    closeModal();
    if (!row) return;

    if (url) {
        const token = $('meta[name="csrf-token"]')?.content;
        fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, Accept: 'application/json' } })
            .then(async (res) => {
                const body = await res.json().catch(() => ({}));
                if (!res.ok) { toast(body.message || 'Could not delete. Please try again.'); return; }
                const table = row.closest('[data-table]');
                row.remove();
                if (table) table.dispatchEvent(new Event('refresh'));
                toast(body.message || 'Deleted.');
            })
            .catch(() => toast('Could not delete. Please try again.'));
        return;
    }

    const table = row.closest('[data-table]');
    row.remove();
    if (table) table.dispatchEvent(new Event('refresh'));
    toast('Deleted (demo)');
});

/* ---------- Tabs ---------- */
$$('[data-tabs]').forEach((wrap) => {
    const tabs = $$('[data-tab]', wrap);
    const show = (name) => {
        tabs.forEach((t) => t.classList.toggle('active', t.dataset.tab === name));
        $$('[data-panel]').forEach((p) => {
            if (p.closest('[data-tabs-root]') === wrap.closest('[data-tabs-root]')) p.classList.toggle('hidden', p.dataset.panel !== name);
        });
    };
    tabs.forEach((t) => t.addEventListener('click', () => show(t.dataset.tab)));
});

/* ---------- Tables: search, filter, pagination ---------- */
$$('[data-table]').forEach((table) => {
    const id = table.dataset.table;
    const search = $(`[data-table-search="${id}"]`);
    const filters = $$(`[data-table-filter="${id}"]`);
    const pager = $(`[data-pager="${id}"]`);
    const count = $(`[data-table-count="${id}"]`);
    const size = pager ? Number(pager.dataset.size) : 9999;
    let page = 1;

    const rows = () => $$('tbody tr:not([data-empty])', table);
    const empty = $('[data-empty]', table);

    const render = () => {
        const q = (search?.value || '').toLowerCase().trim();
        const matches = rows().filter((tr) => {
            const okQ = !q || tr.textContent.toLowerCase().includes(q);
            const okF = filters.every((f) => {
                if (!f.value) return true;
                const cell = tr.children[Number(f.dataset.col)];
                return cell && cell.textContent.trim().toLowerCase().includes(f.value.toLowerCase());
            });
            return okQ && okF;
        });
        const pages = Math.max(1, Math.ceil(matches.length / size));
        page = Math.min(page, pages);
        rows().forEach((tr) => tr.classList.add('hidden'));
        matches.slice((page - 1) * size, page * size).forEach((tr) => tr.classList.remove('hidden'));
        if (empty) empty.classList.toggle('hidden', matches.length > 0);
        if (count) count.textContent = `${matches.length} result${matches.length === 1 ? '' : 's'}`;

        if (pager) {
            const from = matches.length ? (page - 1) * size + 1 : 0;
            const to = Math.min(page * size, matches.length);
            $('[data-pager-info]', pager).textContent = `Showing ${from}–${to} of ${matches.length}`;
            const box = $('[data-pager-buttons]', pager);
            box.innerHTML = '';
            const mk = (label, p, { active = false, disabled = false } = {}) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = label;
                b.disabled = disabled;
                b.className = `btn btn-sm !min-w-8 !px-2 ${active ? '!bg-brand-50 !border-brand-200 !text-brand-700' : ''} ${disabled ? 'opacity-50 !cursor-not-allowed' : ''}`;
                b.addEventListener('click', () => { page = p; render(); });
                box.appendChild(b);
            };
            mk('Previous', page - 1, { disabled: page === 1 });
            for (let i = 1; i <= pages; i++) mk(String(i), i, { active: i === page });
            mk('Next', page + 1, { disabled: page === pages });
        }
    };

    $$(`[data-clear-filters="${id}"]`).forEach((b) => b.addEventListener('click', () => {
        if (search) search.value = '';
        filters.forEach((f) => {
            f.value = '';
            if (window.jQuery && window.jQuery(f).data('select2')) window.jQuery(f).val('').trigger('change');
        });
        page = 1; render();
    }));
    search?.addEventListener('input', () => { page = 1; render(); });
    filters.forEach((f) => {
        const onChange = () => { page = 1; render(); };
        f.addEventListener('change', onChange);
        if (window.jQuery) window.jQuery(f).on('change select2:select select2:clear select2:unselect', onChange);
    });
    table.addEventListener('refresh', render);

    // Brief skeleton on first paint so the table feels like it is loading, not popping in.
    if (reduceMotion) {
        render();
    } else {
        const cols = $$('thead th', table).length;
        const tbody = $('tbody', table);
        const sk = [...Array(Math.min(size, 5))].map(() => {
            const tr = document.createElement('tr');
            tr.dataset.skeleton = '1';
            tr.innerHTML = Array.from({ length: cols }, (_, i) => `<td><span class="skeleton" style="width:${i === 0 ? 70 : 40 + ((i * 17) % 40)}%"></span></td>`).join('');
            return tr;
        });
        rows().forEach((tr) => tr.classList.add('hidden'));
        sk.forEach((tr) => tbody.appendChild(tr));
        setTimeout(() => { sk.forEach((tr) => tr.remove()); render(); }, 380);
    }
});

/* ---------- Roles & permissions: pick a role, swap the matrix ---------- */
$$('[data-edit-pick]').forEach((btn) => {
    btn.addEventListener('click', () => {
        $$('[data-edit-panel]').forEach((p) => p.classList.toggle('hidden', p.dataset.editPanel !== btn.dataset.editPick));
    });
});
$$('[data-edit-cancel]').forEach((btn) => {
    btn.addEventListener('click', () => $$('[data-edit-panel]').forEach((p) => p.classList.add('hidden')));
});

$$('[data-check-all]').forEach((cb) => {
    cb.addEventListener('change', () => {
        $$('input[type=checkbox]', cb.closest('tr')).forEach((c) => { c.checked = cb.checked; });
    });
});

/* ---------- Hall seat map: click a seat to toggle selection ---------- */
$$('[data-seat]').forEach((s) => {
    s.addEventListener('click', () => {
        if (s.dataset.seat === 'taken') return;
        const on = s.dataset.picked === '1';
        s.dataset.picked = on ? '0' : '1';
        s.classList.toggle('!bg-brand-600', !on);
        s.classList.toggle('!text-white', !on);
        const n = $$('[data-picked="1"]').length;
        const out = $('[data-seat-summary]');
        if (out) out.textContent = n ? `${n} seat${n > 1 ? 's' : ''} selected` : 'Click an available seat to select it';
    });
});

/* ---------- Toggle switches ---------- */
$$('[data-switch]').forEach((sw) => {
    sw.addEventListener('click', () => {
        const on = sw.getAttribute('aria-checked') !== 'true';
        sw.setAttribute('aria-checked', String(on));
        const input = sw.previousElementSibling;
        if (input && input.type === 'checkbox') input.checked = on;
    });
});

/* ---------- Auth screens (frontend only) ---------- */
$$('form[data-redirect]').forEach((f) => f.addEventListener('submit', (e) => { e.preventDefault(); location.href = f.dataset.redirect; }));
$$('form[data-notify]').forEach((f) => f.addEventListener('submit', (e) => { e.preventDefault(); $('[data-notice]').classList.remove('hidden'); }));
$$('[data-pw-toggle]').forEach((b) => b.addEventListener('click', () => {
    const i = b.previousElementSibling;
    i.type = i.type === 'password' ? 'text' : 'password';
    b.textContent = i.type === 'password' ? 'Show' : 'Hide';
}));

/* ---------- Animated stat counters ---------- */
$$('[data-count]').forEach((el) => {
    const m = el.textContent.trim().match(/^(\D*?)([\d,]+(?:\.\d+)?)(.*)$/);
    if (!m || reduceMotion) return;
    const [, pre, num, post] = m;
    const target = parseFloat(num.replace(/,/g, ''));
    const decimals = (num.split('.')[1] || '').length;
    const fmt = (v) => v.toLocaleString('en-IN', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    const start = performance.now();
    const dur = 900;
    el.textContent = `${pre}${fmt(0)}${post}`;
    const tick = (now) => {
        const p = Math.min(1, (now - start) / dur);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = `${pre}${fmt(target * eased)}${post}`;
        if (p < 1) requestAnimationFrame(tick); else el.textContent = `${pre}${num}${post}`;
    };
    requestAnimationFrame(tick);
});

/* ---------- Notifications ---------- */
$('[data-mark-read]')?.addEventListener('click', (e) => {
    e.stopPropagation();
    $$('[data-unread]').forEach((d) => d.classList.replace(d.classList.item(d.classList.length - 1), 'bg-slate-300'));
    $('[data-bell-dot]')?.remove();
    toast('All caught up');
});

/* ---------- Page transitions: fade out before navigating ---------- */
document.addEventListener('click', (e) => {
    const a = e.target.closest('a[href]');
    if (!a || reduceMotion || (a.getAttribute('href') || '#').startsWith('#') || e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || a.target === '_blank') return;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin || (url.pathname === location.pathname && url.hash)) return;
    e.preventDefault();
    document.body.classList.add('is-leaving');
    setTimeout(() => { location.href = a.href; }, 140);
});
window.addEventListener('pageshow', () => document.body.classList.remove('is-leaving'));
