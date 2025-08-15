@props(['title' => null])

<div
    x-data="{ open: @entangle($attributes->wire('model')) }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-modal="true" role="dialog">

    <div class="fixed inset-0 bg-gray-900/50" @click="open=false"></div>

    <div class="relative bg-white w-full max-w-xl mx-4 rounded-xl shadow-xl ring-1 ring-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                {{ $title ?? ($title = $header ?? null) }}
                {{ $title ? '' : '' }}
            </h3>
            <button @click="open=false" class="p-2 rounded-md hover:bg-gray-100">
                <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 11-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 11-1.414-1.414L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
            </button>
        </div>

        <div class="px-6 py-5">
            {{ $slot }}
        </div>

        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>
