@props(['name'])
<span {{ $attributes->class(['technology-logo']) }} data-technology-logo="{{ $name }}" aria-hidden="true" style="--technology-logo: url('{{ asset('assets/brands/'.$name.'.svg') }}')"></span>
