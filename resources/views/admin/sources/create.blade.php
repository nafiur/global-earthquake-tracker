<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Data Source') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($types->isEmpty())
                        <div class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800">
                            No source types found. <a href="{{ route('admin.source-types.create') }}" class="font-semibold underline">Create a source type</a> before adding a new source.
                        </div>
                    @endif

                    <form action="{{ route('admin.sources.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" for="name">Display Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" for="url">API URL</label>
                            <input type="text" id="url" name="url" value="{{ old('url') }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                            @error('url')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1" for="type_id">Source Type</label>
                            <select id="type_id" name="type_id" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" @disabled($types->isEmpty())>
                                <option value="">Select a type</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('type_id') == $type->id)>
                                        {{ $type->name }} ({{ strtoupper($type->key) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('type_id')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked(old('is_active'))>
                            <label for="is_active" class="ml-2 text-sm text-gray-600 dark:text-gray-300">Set as active source after saving</label>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded-md text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                Cancel
                            </a>
                            <button type="submit" @disabled($types->isEmpty()) class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition disabled:opacity-60">
                                Create Source
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
