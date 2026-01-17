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
<style>
    :root {
        --color-neutral-800: #262626;
        --color-white: #ffffff;
        
        --color-accent: var(--color-neutral-800);
        --color-accent-content: var(--color-neutral-800);
        --color-accent-foreground: var(--color-white);
    }
    .dark {
        --color-accent: var(--color-white);
        --color-accent-content: var(--color-white);
        --color-accent-foreground: var(--color-neutral-800);
    }
    
    [data-flux-field]:not(ui-radio, ui-checkbox) {
        display: grid;
        gap: 0.5rem;
    }
    [data-flux-label] {
        margin-bottom: 0 !important;
        line-height: 1.25 !important;
    }
    input:focus[data-flux-control],
    textarea:focus[data-flux-control],
    select:focus[data-flux-control] {
        outline: 2px solid transparent;
        outline-offset: 2px;
        --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
        --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(2px + var(--tw-ring-offset-width)) var(--tw-ring-color);
        box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
        --tw-ring-offset-width: 2px;
        --tw-ring-color: var(--color-accent);
        --tw-ring-offset-color: var(--color-accent-foreground);
    }
</style>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Custom Dashboard JS -->
<script src="{{ asset('js/dashboard.js') }}"></script>

@fluxAppearance
