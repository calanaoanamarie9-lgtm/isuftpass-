{{--
    Single source for the tab/app icon and PWA metadata.

    Every standalone <head> in the app includes this partial, so the favicon,
    manifest link and theme colour can never drift from page to page. The icon
    files themselves are derived from img/isufstpass-logo.png by a one-off GD
    script (see public/img/icons/) — regenerate rather than hand-edit them.

    theme-color matches the sidebar and mobile top bar (#12347d) so the browser
    chrome blends with the app chrome. status-bar-style is "default" rather than
    "black-translucent": on iOS a home-screen launch would otherwise start the
    document at y=0 under the status bar and hide the top of the sticky header.
--}}
<meta name="theme-color" content="#12347d">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('img/icons/icon-192.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/icons/apple-touch-icon.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="ISUFSTPASS">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
