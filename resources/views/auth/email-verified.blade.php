<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - ISUFSTPASS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @include('partials.pwa-head')
</head>

<body class="min-h-screen bg-slate-50">

<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: 'success',
        title: 'Email Verified!',
        html: `
            <p style="color:#64748b">
                Your ISUFSTPASS email address has been
                successfully verified.
            </p>
        `,
        confirmButtonText: @js($label),
        confirmButtonColor: '#1d4ed8',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'rounded-3xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then(() => {
        window.location.href = @js($destination);
    });
});
</script>
</body>

</html>
