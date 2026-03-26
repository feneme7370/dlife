<div>
    <flux:modal.trigger name="add-template">
        <flux:button variant="ghost" icon="plus"></flux:button>
    </flux:modal.trigger>

    {{-- modal para agregar templates --}}
    <flux:modal name="add-template" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Agregue una plantilla</flux:heading>
                <flux:text class="mt-2">Agregue una nueva plantilla para su nota.</flux:text>
            </div>

            <flux:input wire:model="title_template" label="Titulo" placeholder="Titulo de la plantilla" />

            <x-libraries.quill-textarea-form 
                id_quill="editor_create_content" 
                name="content_template"
                rows="15" 
                placeholder="{{ __('Descripcion') }}" model="content_template"
            />

            <div class="flex">
                <flux:spacer />

                <flux:button wire:click="addTemplate" variant="primary">Agregar</flux:button>
            </div>
        </div>
    </flux:modal>
</div>