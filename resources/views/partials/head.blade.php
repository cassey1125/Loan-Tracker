<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Flux CSS -->
<link href="{{ asset('css/flux.css') }}" rel="stylesheet">
<!-- Custom Style CSS -->
<link href="/src/style.css" rel="stylesheet">

<!-- Tailwind CSS (CDN) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
                },
                colors: {
                    zinc: {
                        50: '#fafafa',
                        100: '#f5f5f5',
                        200: '#e5e5e5',
                        300: '#d4d4d4',
                        400: '#a3a3a3',
                        500: '#737373',
                        600: '#525252',
                        700: '#404040',
                        800: '#262626',
                        900: '#171717',
                        950: '#0a0a0a',
                    },
                    accent: {
                        DEFAULT: 'var(--color-accent)',
                        content: 'var(--color-accent-content)',
                        foreground: 'var(--color-accent-foreground)',
                    }
                }
            }
        }
    }
</script>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Custom Dashboard JS -->
<script src="{{ asset('js/dashboard.js') }}"></script>

@fluxAppearance
