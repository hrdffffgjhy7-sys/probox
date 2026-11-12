import './bootstrap';

const ready = (fn) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
};

ready(() => {
    const root = document.documentElement;

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const dark = root.classList.toggle('dark');
            try {
                localStorage.setItem('theme', dark ? 'dark' : 'light');
            } catch (e) {
                /* ignore */
            }
        });
    });

    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    const closeSidebar = () => {
        if (!sidebar) return;
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        if (backdrop) backdrop.classList.add('hidden');
    };

    document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!sidebar) return;
            const hidden = sidebar.classList.contains('-translate-x-full');
            if (hidden) {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (backdrop) backdrop.classList.remove('hidden');
            } else {
                closeSidebar();
            }
        });
    });

    backdrop?.addEventListener('click', closeSidebar);

    document.querySelectorAll('[data-dropdown-toggle]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const id = button.getAttribute('data-dropdown-toggle');
            const menu = document.getElementById(id);
            if (!menu) return;
            document.querySelectorAll('[data-dropdown]').forEach((other) => {
                if (other !== menu) other.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown]').forEach((menu) => menu.classList.add('hidden'));
    });

    document.querySelectorAll('[data-dropdown]').forEach((menu) => {
        menu.addEventListener('click', (event) => event.stopPropagation());
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-period-select]').forEach((select) => {
        const custom = document.querySelector('[data-period-custom]');
        if (!custom) return;
        const toggle = () => custom.classList.toggle('hidden', select.value !== 'custom');
        select.addEventListener('change', toggle);
        toggle();
    });

    initPurchaseForm();
});

function parseNumber(value) {
    const n = parseFloat(String(value ?? '').replace(/,/g, ''));
    return Number.isFinite(n) ? n : 0;
}

function formatMoney(value) {
    return (Math.round(value * 100) / 100).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function initPurchaseForm() {
    const form = document.querySelector('[data-purchase-form]');
    if (!form) return;

    const body = form.querySelector('[data-items-body]');
    const template = form.querySelector('template[data-item-template]');
    if (!body || !template) return;

    let index = parseInt(body.getAttribute('data-next-index') || '0', 10);

    const addRow = () => {
        const html = template.innerHTML.replace(/__INDEX__/g, String(index));
        index += 1;
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        const row = wrapper.querySelector('tr');
        if (row) body.appendChild(row);
        recalc();
    };

    const recalc = () => {
        let subtotal = 0;
        let itemDiscount = 0;
        let itemTax = 0;

        body.querySelectorAll('[data-item-row]').forEach((row) => {
            const qty = parseNumber(row.querySelector('[data-field="quantity"]')?.value);
            const rate = parseNumber(row.querySelector('[data-field="rate"]')?.value);
            const discount = parseNumber(row.querySelector('[data-field="discount"]')?.value);
            const tax = parseNumber(row.querySelector('[data-field="tax"]')?.value);
            const line = qty * rate;
            const total = Math.max(line + tax - discount, 0);
            const output = row.querySelector('[data-line-total]');
            if (output) output.textContent = formatMoney(total);
            subtotal += line;
            itemDiscount += discount;
            itemTax += tax;
        });

        const discount = parseNumber(form.querySelector('[data-summary="discount"]')?.value);
        const tax = parseNumber(form.querySelector('[data-summary="tax"]')?.value);
        const additional = parseNumber(form.querySelector('[data-summary="additional"]')?.value);
        const paid = parseNumber(form.querySelector('[data-summary="paid"]')?.value);
        const grand = Math.max(subtotal + itemTax + tax - itemDiscount - discount + additional, 0);
        const remaining = Math.max(grand - paid, 0);

        const set = (name, value) => {
            const el = form.querySelector(`[data-output="${name}"]`);
            if (el) el.textContent = formatMoney(value);
        };

        set('subtotal', subtotal);
        set('grand', grand);
        set('remaining', remaining);
    };

    form.addEventListener('click', (event) => {
        const add = event.target.closest('[data-add-item]');
        if (add) {
            event.preventDefault();
            addRow();
            return;
        }

        const remove = event.target.closest('[data-remove-item]');
        if (remove) {
            event.preventDefault();
            const rows = body.querySelectorAll('[data-item-row]');
            if (rows.length > 1) {
                remove.closest('[data-item-row]')?.remove();
            } else {
                rows[0]?.querySelectorAll('input').forEach((input) => {
                    if (input.type !== 'hidden') input.value = '';
                });
            }
            recalc();
        }
    });

    body.addEventListener('input', recalc);
    body.addEventListener('change', (event) => {
        const select = event.target.closest('[data-product-select]');
        if (select) {
            const option = select.options[select.selectedIndex];
            const row = select.closest('[data-item-row]');
            if (row && option) {
                const name = row.querySelector('[data-field="product_name"]');
                const unit = row.querySelector('[data-field="unit"]');
                const rate = row.querySelector('[data-field="rate"]');
                if (name && option.dataset.name) name.value = option.dataset.name;
                if (unit && option.dataset.unit) unit.value = option.dataset.unit;
                if (rate && option.dataset.rate) rate.value = option.dataset.rate;
            }
        }
        recalc();
    });

    form.querySelectorAll('[data-summary]').forEach((input) => input.addEventListener('input', recalc));

    const paymentSupplier = document.querySelector('[data-payment-supplier]');
    const purchaseSelect = document.querySelector('[data-payment-purchase]');
    if (paymentSupplier && purchaseSelect) {
        const filter = () => {
            const supplier = paymentSupplier.value;
            Array.from(purchaseSelect.options).forEach((option, i) => {
                if (i === 0) return;
                const match = !supplier || option.dataset.supplier === supplier;
                option.hidden = !match;
                option.disabled = !match;
            });
            if (purchaseSelect.selectedOptions[0]?.disabled) purchaseSelect.value = '';
        };
        paymentSupplier.addEventListener('change', filter);
        filter();
    }

    recalc();
}
