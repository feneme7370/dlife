<?php

use Livewire\Component;

new class extends Component
{
    public $start;
    public $end;
    public $diaries;
    public $mood;

    //////////////////////////////////////////////////////////////////// FUNCIONES PARA FILTRAR
    // mostrar variables en queryString
    protected function queryString(){
        return [
            'start' => [ 'as' => 's' ],
            'end' => [ 'as' => 'e' ],
        ];
    }

    //////////////////////////////////////////////////////////////////// PRE CARGAR DATOS
    public function mount()
    {
        $this->diaries = \App\Models\Page\Diary::
            where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->whereBetween('day', [$this->start, $this->end])
            ->orderBy('day', 'desc')
            ->get();

        $this->mood = \App\Models\Page\Diary::humor_status();
    }

};
?>

<div class="text-sm text-gray-800 dark:text-gray-300 space-y-5">
    @foreach ($diaries as $item)
        <div>
            <p class="italic">{{ $item->day->format('Y-m-d') . ' | ' . $this->mood[$item->status] ?? 'Desconocido' }}</p>
            <p class="text-lg font-bold">{{ $item->title }}</p>
            <p>{!! $item->content !!}</p>
            <p>----------------------------</p>
        </div>
    @endforeach
</div>