{{--
    A page kept in the browser can ask for a bundle the last deploy deleted:
    Vite gives every build a new file name and takes the old one with it. The
    script 404s, Alpine never starts, and every button on the page stops
    answering without a word about why.

    So the page checks for itself. If Alpine is missing when the page has
    finished loading, the page reloads once - which brings the HTML that
    names the bundle that is actually there. Should even that not be
    enough, it says so rather than leaving a dead page to be puzzled over.
--}}
<script>
    (function () {
        window.addEventListener('load', function () {
            // The bundle says it is ready once Alpine has started. A page
            // whose script 404ed - or whose script failed before it could
            // start - never hears from it.
            if (window.__isufstpassReady) {
                return;
            }

            var key = 'isuftpass:asset-reload';
            var now = Date.now();
            var last = 0;

            try {
                last = Number(window.sessionStorage.getItem(key) || 0);
            } catch (error) {
                last = 0;
            }

            // One reload, then a word about it - never a loop.
            if (now - last > 30000) {
                try {
                    window.sessionStorage.setItem(key, String(now));
                } catch (error) {
                    // A browser that will not keep the marker still gets one
                    // reload; the notice below covers the rest.
                }

                window.location.reload();

                return;
            }

            var notice = document.createElement('div');

            notice.className = 'fixed bottom-4 left-1/2 -translate-x-1/2 z-[9999]'
                + ' rounded-xl bg-gray-900 px-4 py-3 text-sm font-semibold'
                + ' text-white shadow-lg';

            notice.textContent = 'This page is out of date. Press Ctrl+F5 to refresh it.';

            document.body.appendChild(notice);
        });
    })();
</script>
