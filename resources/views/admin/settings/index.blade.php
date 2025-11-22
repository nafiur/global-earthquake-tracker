<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Application Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <h3 class="text-lg font-medium mb-4">News API Configuration</h3>
                            
                            <div class="mb-4">
                                <label for="news_api_key" class="block text-sm font-medium mb-2">
                                    NewsAPI.org API Key
                                </label>
                                <input 
                                    type="text" 
                                    name="news_api_key" 
                                    id="news_api_key" 
                                    value="{{ old('news_api_key', $newsApiKey) }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm"
                                    placeholder="Enter your NewsAPI.org API key"
                                >
                                @error('news_api_key')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Get a free API key from <a href="https://newsapi.org" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">newsapi.org</a>. 
                                    This key is used to fetch earthquake-related news articles.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <button 
                                type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                            >
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
