@props([
    'size' => 'md',          
    'variant' => 'primary',
    'startIcon' => null,
    'endIcon' => null,
    'className' => '',
    'disabled' => false,
])

@php
    // Base classes
    $base = 'inline-flex items-center justify-center font-medium gap-2 rounded-lg transition';

    // Size map
    $sizeMap = [
        'sm' => 'px-4 py-3 text-sm',
        'md' => 'px-5 py-3.5 text-sm',
    ];
    $sizeClass = $sizeMap[$size] ?? $sizeMap['md'];

    // Variant map
    $variantMap = [
        // Primary (Vibrant Corporate Blue)
        'primary' => 'bg-blue-600 text-white shadow-theme-xs hover:bg-blue-700 active:bg-blue-800 disabled:bg-blue-300 dark:bg-blue-500 dark:hover:bg-blue-600',
        
        // Outline (Clean Slate Minimalist)
        'outline' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 active:bg-slate-100 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700 dark:hover:bg-slate-700/50',

        // Success (Crisp Emerald Green)
        'success' => 'bg-emerald-600 text-white shadow-theme-xs hover:bg-emerald-700 active:bg-emerald-800 disabled:bg-emerald-300 dark:bg-emerald-500 dark:hover:bg-emerald-600',

        // Danger (Vivid Crimson Red)
        'danger' => 'bg-rose-600 text-white shadow-theme-xs hover:bg-rose-700 active:bg-rose-800 disabled:bg-rose-300 dark:bg-rose-500 dark:hover:bg-rose-600',

        // Warning (Bright Amber/Marigold Orange)
        'warning' => 'bg-amber-500 text-white shadow-theme-xs hover:bg-amber-600 active:bg-amber-700 disabled:bg-amber-300 dark:bg-amber-500 dark:text-slate-950 dark:hover:bg-amber-400',

        // Info (Electric Sky Blue)
        'info' => 'bg-sky-500 text-white shadow-theme-xs hover:bg-sky-600 active:bg-sky-700 disabled:bg-sky-300 dark:bg-sky-400 dark:text-slate-950 dark:hover:bg-sky-300',

        // Secondary (Sleek Slate/Charcoal)
        'secondary' => 'bg-slate-600 text-white shadow-theme-xs hover:bg-slate-700 active:bg-slate-800 disabled:bg-slate-300 dark:bg-slate-700 dark:hover:bg-slate-600',
    ];
    $variantClass = $variantMap[$variant] ?? $variantMap['primary'];

    // disabled classes
    $disabledClass = $disabled ? 'cursor-not-allowed opacity-50' : '';

    // final classes (merge user className too)
    $classes = trim("{$base} {$sizeClass} {$variantClass} {$className} {$disabledClass}");
@endphp

<button
    {{ $attributes->merge(['class' => $classes, 'type' => $attributes->get('type', 'button')]) }}
    @if($disabled) disabled @endif
>
    {{-- start icon: priority — named slot 'startIcon' first, then startIcon prop if it's a HtmlString --}}
    @if (isset($__env) && $slot->isEmpty() === false) @endif

    @hasSection('startIcon')
        <span class="flex items-center">
            @yield('startIcon')
        </span>
    @elseif($startIcon)
        <span class="flex items-center">{!! $startIcon !!}</span>
    @endif

    {{-- main slot --}}
    {{ $slot }}

    {{-- end icon: named slot 'endIcon' first, then endIcon prop --}}
    @hasSection('endIcon')
        <span class="flex items-center">
            @yield('endIcon')
        </span>
    @elseif($endIcon)
        <span class="flex items-center">{!! $endIcon !!}</span>
    @endif
</button>
