@if ($errors->any())
<div class="bg-red-100 border border-red-400 text-red-700 p-1 rounded">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif