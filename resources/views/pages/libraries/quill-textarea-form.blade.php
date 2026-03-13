@props(['id_quill' => '','model' => '','model_data' => '', 'placeholder' => 'Descripcion','disabled' => false, 'height' => '300'])

<div class="col-span-12" id="quill_editor_css" wire:ignore
{!! $attributes->merge(['class' => '
    resize-none 
    block w-full 
    text-sm rounded-lg shadow-md
    my-1 p-2 
    bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-purple-600 focus:border-purple-600
    ']) 
!!}
>
<div id="{{ $id_quill }}" style="height: {{ $height }}px;">
    
</div>
<input type="hidden" wire:model="{{ $model }}" id="{{ $model }}">


<script>
    document.addEventListener("livewire:initialized", function () {

        var quill = new Quill("#{{ $id_quill }}", {
            theme: "snow",
            placeholder: "{{ $placeholder }}",
            modules: {
                toolbar: [
                    ["clean"], // Eliminar formato
                    // [{ header: [false, 1, 2, 3] }], // Tamaño de encabezado
                    [{ size: ["small",false , "large", "huge"] }], // Tamaños de textonpm install quill-image-resize-module --save
                    ["bold", "italic", "underline", "strike"], // Formatos de texto
                    // [{ color: [] }, { background: [] }], // Color de texto y fondo
                    [{ list: "ordered" }, { list: "bullet" }], // Listas
                    // [{ script: "sub" }, { script: "super" }], // Subíndice y superíndice
                    // [{ indent: "-1" }, { indent: "+1" }], // Sangría
                    // [{ direction: "rtl" }], // Dirección del texto
                    // [{ font: [] }], // Tipo de fuente
                    // [{ align: [] }], // Alineación del texto
                    // ["link", "image", "video"], // Enlaces, imágenes y videos
                    ["link"], // Enlaces, imágenes y videos
                    
                ],
            }
        });

         // Cargar contenido guardado al iniciar
        quill.root.innerHTML = @json($this->$model);

        quill.on("text-change", function () {
            let content = quill.root.innerHTML;
            @this.set("{{ $model }}", content)
        });

        Livewire.hook("morphed",  () => {
            quill.on("text-change", function () {
                let content = quill.root.innerHTML;
                @this.set("{{ $model }}", content)
            });
        })

    });
</script>

</div>