<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

<script src="{{asset('data/libraries/jquery/jquery.min.js')}}"  ></script>
<link rel="stylesheet" href="{{asset('data/libraries/lightbox/css/lightbox.min.css')}}">

<script src="{{asset('data/libraries/quill/quill.js')}}"></script>
<link href="{{asset('data/libraries/quill/quill.snow.css')}}" rel="stylesheet">

{{-- <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script> --}}