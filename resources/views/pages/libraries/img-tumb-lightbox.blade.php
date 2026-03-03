@props(['uri' => '', 'album' => '', 'name' => '', 'class_w_h' => 'w-full h-full', 'class' => '', 'tumb' => true, 'temporary' => false])


    {{-- @if ($temporary)
        @if($name && !$name == '' && !$name == null)
            <a href="{{asset($name)}}" data-lightbox="{{$name}}">
                <img src="{{asset($name)}}" alt="imagen portada" class="{{ $class_w_h }} {{ $class }}  object-cover rounded-sm"/>
            </a>
        @endif
    @else
        @if($name && !$name == '' && !$name == null && file_exists($uri .$name))
            <a href="{{asset( $uri .$name)}}" data-lightbox="{{$name}}">
                <img src="{{asset( $uri . ($tumb ? ($tumb ? 'tumb_' : '') : '') . $name)}}" alt="imagen portada" class=" {{ $class_w_h }} {{ $class }}  object-cover rounded-sm"/>
            </a>
        @endif

    @endif --}}
    
    <a href="{{ $uri }}" data-lightbox="{{ $album }}" data-title="{{ $album }}">
        <img src="{{ $uri }}" class="{{ $class_w_h }} {{ $class }} object-cover rounded-sm" alt="imagen portada">
    </a>
    

    @push('lightbox')
        <script>
            lightbox.option({
            'alwaysShowNavOnTouchDevices': true,
            'showImageNumberLabel': true,
            'imageFadeDuration': 50,
            'resizeDuration': 50,
            'fadeDuration': 50,
            'disableScrolling': true,
            'wrapAround': true,
            'albumLabel': true,
            
            })
        </script>
    @endpush



