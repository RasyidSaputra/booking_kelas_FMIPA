@props(['name'])
<svg {{ $attributes->class(['dash-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 5h2m4 0h2"/> @break
        @case('building') <path d="M5 21V3h14v18M2 21h20M9 21v-6h6v6M9 7h1m4 0h1M9 11h1m4 0h1"/> @break
        @case('list') <rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 3h6v3H9zm0 8h6m-6 5h6"/> @break
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/> @break
        @case('arrow') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('chevron-left') <path d="m14 6-6 6 6 6"/> @break
        @case('chevron-right') <path d="m10 6 6 6-6 6"/> @break
        @case('chevron-down') <path d="m6 9 6 6 6-6"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('user') <circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/> @break
        @case('logout') <path d="M9 4H4v16h5m5-12 4 4-4 4m-5-4h11"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('pin') <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/> @break
        @case('users') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3m2-17a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 4v3"/> @break
        @case('info') <circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v.1"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('lock') <rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/> @break
        @case('shield') <path d="m12 3 8 3v6c0 4-4 7-8 9-4-2-8-5-8-9V6l8-3Z"/><path d="m8 12 3 3 5-5"/> @break
        @case('eye') <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/> @break
        @case('eye-off') <path d="m3 3 18 18M10 5.2A10 10 0 0 1 12 5c6 0 10 7 10 7a19 19 0 0 1-3.3 4.1M6 6a20 20 0 0 0-4 6s4 7 10 7a11 11 0 0 0 4.4-.9m-6.5-8.2a3 3 0 0 0 4.2 4.2"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
    @endswitch
</svg>
