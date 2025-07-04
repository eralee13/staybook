@livewireStyles
<div>
    <input type="radio" wire:model="selectedOption" name="options" value="1"> Option 1
    <input type="radio" wire:model="selectedOption" name="options" value="2"> Option 2
    
    <p>Выбрано: {{ $selectedOption }}</p>
</div>
@livewireScripts