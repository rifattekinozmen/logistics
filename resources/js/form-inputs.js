/**
 * Form input davranışları: yazım denetimi (spellcheck) ve büyük/küçük harf düzeltmesi.
 * .form-control sınıflı metin alanlarına uygulanır.
 */

const EXCLUDED_FOR_CAPITALIZE = [
    'email', 'phone', 'telefon', 'latitude', 'longitude', 'tckn',
    'coordinates', 'file_path', 'tax_number', 'paste', 'search',
    'mobil_telefon', 'acil_iletisim', 'kimlik_seri_no', 'password',
];

function shouldApplyCapitalize(el) {
    if (el.dataset.noCapitalize !== undefined) {
        return false;
    }
    const type = (el.getAttribute('type') || 'text').toLowerCase();
    if (['email', 'number', 'date', 'datetime-local', 'month', 'file', 'password', 'url', 'hidden'].includes(type)) {
        return false;
    }
    if (el.getAttribute('inputmode') === 'decimal') {
        return false;
    }
    const name = (el.getAttribute('name') || el.id || '').toLowerCase();
    return !EXCLUDED_FOR_CAPITALIZE.some((key) => name.includes(key));
}

function toTitleCase(str) {
    if (!str || typeof str !== 'string') {
        return str;
    }
    return str
        .trim()
        .split(/\s+/)
        .map((word) => word.charAt(0).toLocaleUpperCase('tr-TR') + word.slice(1).toLocaleLowerCase('tr-TR'))
        .join(' ');
}

function toSentenceCase(str) {
    if (!str || typeof str !== 'string') {
        return str;
    }
    const trimmed = str.trim();
    if (!trimmed) {
        return str;
    }
    const sentences = trimmed.split(/([.!?]\s*)/);
    return sentences
        .map((part, i) => {
            if (/^[.!?]\s*$/.test(part)) {
                return part;
            }
            return part.charAt(0).toLocaleUpperCase('tr-TR') + part.slice(1).toLocaleLowerCase('tr-TR');
        })
        .join('');
}

function isTextInput(el) {
    if (!el || !el.classList || !el.classList.contains('form-control')) {
        return false;
    }
    const tag = el.tagName.toLowerCase();
    if (tag === 'textarea') {
        return true;
    }
    if (tag !== 'input') {
        return false;
    }
    const type = (el.getAttribute('type') || 'text').toLowerCase();
    return type === 'text' || type === 'search' || type === 'email';
}

function setupElement(el) {
    if (!isTextInput(el)) {
        return;
    }
    el.setAttribute('spellcheck', 'true');
    el.setAttribute('lang', 'tr');
    if (el.tagName.toLowerCase() === 'textarea') {
        el.setAttribute('autocapitalize', 'sentences');
    } else if ((el.getAttribute('type') || '').toLowerCase() === 'email') {
        el.setAttribute('autocapitalize', 'none');
    } else {
        el.setAttribute('autocapitalize', 'words');
    }
    if (!el.dataset.formInputsInit) {
        el.dataset.formInputsInit = '1';
        el.addEventListener('blur', handleBlur);
    }
}

function handleBlur(e) {
    const el = e.target;
    if (!isTextInput(el) || !shouldApplyCapitalize(el)) {
        return;
    }
    const tag = el.tagName.toLowerCase();
    const value = el.value;
    if (tag === 'textarea') {
        el.value = toSentenceCase(value);
    } else {
        el.value = toTitleCase(value);
    }
}

function init() {
    document.querySelectorAll('.form-control').forEach(setupElement);

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    if (node.classList && node.classList.contains('form-control')) {
                        setupElement(node);
                    }
                    node.querySelectorAll?.('.form-control').forEach(setupElement);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
