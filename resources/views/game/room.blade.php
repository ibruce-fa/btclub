<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Game Room') }}
        </h2>
    </x-slot>

    <div class="py-4">
{{--        MODAL --}}
        <livewire:live-game-room :game="$game" :isOwner="$isOwner"/>
{{--        MODAL --}}

    </div>

</x-app-layout>
