@props([
'highlightedDayss' => [],
'id_calendar' => 'idCal',
])

<div>

    {{-- propiedades inciales --}}
    {{--     public $dayStart;
    public $dayEnd;
    public $highlightedDays = []; --}}

    {{-- montar datos inciales --}}
    {{--     public $date;
    public function mount(){
        $this->date = \Carbon\Carbon::now()->format('Y-m-d');
        $this->diariesQuery();
        $this->highlightedDays = $this->getDays();
    } --}}
     
    {{-- filtrar por fechas --}}
    {{--     public function updatedDayStart(){$this->diariesQuery();}
    public function updatedDayEnd(){$this->diariesQuery();} --}}
    
    {{-- actualizar formula al final --}}
    {{-- #[On('reading-day-selected')]
    public function selectDay($date){
        $this->dayStart = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $this->dayEnd = \Carbon\Carbon::parse($date)->format('Y-m-d');
        $this->diariesQuery();
    } --}}

    {{-- limpiar datos con clearDate --}}
    {{-- public function clearDate(){
        $this->dayStart = \Carbon\Carbon::parse('1900-01-01')->format('Y-m-d');
        $this->dayEnd = \Carbon\Carbon::parse('2100-01-01')->format('Y-m-d');
        $this->search = '';
        $this->selectedCategory = '';
        $this->selectedTag = '';
        $this->diariesQuery();
    } --}}

    <div wire:ignore>
        <div wire:ignore class="text-center my-1" {!! $attributes->merge(['class' => '']) !!}>
            <input id="{{ $id_calendar }}" type="text" hidden readonly />
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            const input = document.getElementById("{{ $id_calendar }}");
            if (!input) return; // si el input no existe todavía, no inicializamos

            if (window.myPicker) {
                window.myPicker.destroy();
            }

                window.myPicker = new Litepicker({
                element: document.getElementById("{{ $id_calendar }}"),
                format: 'YYYY-MM-DD',
                singleMode: true,
                inlineMode: true,
                highlightedDays: @json($highlightedDayss),
                setup: (picker) => {
                    picker.on('selected', (date) => {
                        // Cuando seleccionás un día, lo mandamos a Livewire
                        Livewire.dispatch('reading-day-selected', { date: date.format('YYYY-MM-DD') });
                    });
                }
            });
        });
    </script>

    <style>
        .litepicker .day-item.is-highlighted

        /* .litepicker .day-item.is-selected  */
            {
            background-color: #70157e !important;
            /* azul Tailwind-600 */
            color: #fff !important;
            border-radius: 40% !important;
            /* redondeado */
        }

        .litepicker .day-item.is-inRange,
        .litepicker .day-item.is-start-date,
        .litepicker .day-item.is-end-date {
            background-color: #40024a !important;
            /* azul Tailwind-600 */
            color: #fff !important;
            border-radius: 30% !important;
            /* redondeado */
        }
    </style>
</div>