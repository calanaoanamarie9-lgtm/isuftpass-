import './bootstrap';

import Alpine from 'alpinejs';

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

import './approve-schedule';

window.Alpine = Alpine;
window.Swal = Swal;

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    let action = '';
    try {
        action = new URL(form.action).pathname;
    } catch {
        return;
    }

    if (form.hasAttribute('data-confirm')) {
        event.preventDefault();

        Swal.fire({
            title: form.dataset.confirmTitle || 'Are you sure?',
            text: form.dataset.confirm || 'This action cannot be undone.',
            icon: form.dataset.confirmIcon || 'warning',
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmOk || 'Yes, proceed',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#b91c1c',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });

        return;
    }

    if (! action.endsWith('/logout')) {
        return;
    }

    event.preventDefault();

    Swal.fire({
        title: 'Log out?',
        text: 'You will be signed out of your account.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, log me out',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

// Global button confirmation: every action button asks for confirmation.
document.addEventListener('click', (event) => {
    const button = event.target.closest('button');

    if (! button) {
        return;
    }

    if (button.hasAttribute('data-no-confirm')) {
        return;
    }

    // Skip UI toggles (modal/dropdown openers) that are not inside a form.
    if (button.type === 'button' && ! button.closest('form')) {
        return;
    }

    const form = button.closest('form');

    // Forms that already confirm their own submission (data-confirm, logout).
    if (form && (form.hasAttribute('data-confirm') || form.action.endsWith('/logout'))) {
        return;
    }

    // This click was already confirmed — let the native action proceed.
    if (button.dataset.saConfirmed === '1') {
        delete button.dataset.saConfirmed;
        return;
    }

    let label = button.textContent.trim().replace(/\s+/g, ' ').slice(0, 40) || 'this action';

    event.preventDefault();
    event.stopImmediatePropagation();

    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to proceed with "' + label + '"?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#123b78',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            button.dataset.saConfirmed = '1';
            button.click();
        }
    });
}, true); // capture phase so it runs before page-specific handlers.

Alpine.start();
