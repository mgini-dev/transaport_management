@php($size = $size ?? 'h-4 w-4')
@switch($name)
    @case('home')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/></svg>
        @break
    @case('users')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="3"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a3 3 0 0 1 0 5.75"/></svg>
        @break
    @case('trip')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
        @break
    @case('box')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21 16-9 5-9-5V8l9-5 9 5z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 12v9"/></svg>
        @break
    @case('truck')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 17h4"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/><path d="M14 17V6H3v11"/><path d="M14 9h4l3 3v5h-3"/></svg>
        @break
    @case('id')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2"/><path d="M13 10h6M13 14h4"/></svg>
        @break
    @case('fuel')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h10V5H3z"/><path d="M13 7h3l2 2v7a2 2 0 1 0 4 0V8l-2-2"/></svg>
        @break
    @case('bell')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 17H5l1.4-1.4A2 2 0 0 0 7 14.2V11a5 5 0 1 1 10 0v3.2a2 2 0 0 0 .6 1.4L19 17h-4"/><path d="M9 17a3 3 0 0 0 6 0"/></svg>
        @break
    @case('shield')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V7z"/></svg>
        @break
    @case('scroll')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8a4 4 0 0 0 0-8H8a4 4 0 0 1 0-8h8"/><path d="M8 5v16"/></svg>
        @break
    @case('lock')
        <svg class="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 1 1 8 0v3"/></svg>
        @break
@endswitch
