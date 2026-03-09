    @if (session('success'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-6 right-6 z-50 w-96"
        >
            <flux:callout 
                variant="success"
                icon="check-circle"
                heading="{{ session('success') }}"
            />
        </div>
    @endif
    @if (session('error'))
        <div 
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="fixed top-6 right-6 z-50 w-96"
        >
            <flux:callout 
                variant="error"
                icon="check-circle"
                heading="{{ session('error') }}"
            />
        </div>
    @endif