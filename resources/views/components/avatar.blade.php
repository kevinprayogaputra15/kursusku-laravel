@props(['user', 'size' => 40])
{{-- Logo/avatar profil: lingkaran gradien dengan inisial nama --}}
<span {{ $attributes->class(['kk-avatar']) }}
      style="--kk-avatar-size: {{ (int) $size }}px"
      aria-hidden="true">{{ $user->initials }}</span>
