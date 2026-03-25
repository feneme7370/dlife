@props(['icon' => 'plus', 'title' => null, 'subtitle' => null, 'breadcrumbs' => [], 'createRoute' => null])

<div>
    <div class="mb-1 space-y-1">
        
        <flux:heading size="xl" level="1">
            @if($createRoute)
                <a href="{{ route($createRoute) }}">
                    <flux:button size="xs" variant="ghost" :icon="$icon"></flux:button>
                </a>
            @endif

            {{ $title }}
        </flux:heading>

        {{-- @if($subtitle)
            <flux:text class="text-base">{{ $subtitle }}</flux:text>
        @endif --}}

        <flux:breadcrumbs>
            @foreach($breadcrumbs as $breadcrumb)
                @if(isset($breadcrumb['route']))
                    <flux:breadcrumbs.item href="{{ route($breadcrumb['route']) }}">
                        {{ $breadcrumb['label'] }}
                    </flux:breadcrumbs.item>
                @else
                    <flux:breadcrumbs.item>
                        {{ $breadcrumb['label'] }}
                    </flux:breadcrumbs.item>
                @endif
            @endforeach
        </flux:breadcrumbs>

        <flux:separator variant="subtle" />
    </div>
</div>