<form action="{{ route('admin.documents.previewAssign') }}" method="POST" class="w-full">
    @csrf
    <input type="hidden" name="temp_batch_id" value="{{ $tempBatchId }}">
    <input type="hidden" name="original_filename" value="{{ $file['original_filename'] }}">
    
    <div x-data="{
        search: '',
        results: [],
        selectedClient: null,
        loading: false,
        fetchClients() {
            if (this.search.length < 2) {
                this.results = [];
                return;
            }
            this.loading = true;
            fetch('{{ route('admin.documents.clients.search') }}?temp_batch_id={{ $tempBatchId }}&q=' + encodeURIComponent(this.search))
                .then(res => res.json())
                .then(data => {
                    this.results = data;
                    this.loading = false;
                })
                .catch(() => { this.loading = false; });
        }
    }" class="relative text-left">
        <div class="flex gap-1.5 items-center font-sans">
            <div class="relative flex-1">
                <input type="text" x-model="search" @input.debounce.300ms="fetchClients()" 
                    placeholder="Cari klien..." 
                    class="block w-full text-xs border border-gray-300 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-800 p-2 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:text-white">
                <div x-show="loading" class="absolute right-2 top-2.5">
                    <svg class="animate-spin h-3.5 w-3.5 text-brand-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            <button type="submit" :disabled="!selectedClient" 
                class="bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold py-2 px-3 rounded-lg flex-shrink-0 transition">
                Assign
            </button>
        </div>

        <!-- Search Dropdown Results -->
        <div x-show="results.length > 0" @click.outside="results = []"
            class="absolute right-0 z-50 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-40 overflow-y-auto text-left">
            <template x-for="client in results" :key="client.id">
                <button type="button" @click="selectedClient = client; search = client.name; results = []"
                    class="w-full text-left px-3 py-2 text-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition flex flex-col">
                    <span class="font-semibold text-gray-900 dark:text-white" x-text="client.name"></span>
                    <span class="text-[10px] text-gray-500" x-text="'JPN: ' + client.no_jp"></span>
                </button>
            </template>
        </div>

        <!-- Hidden input -->
        <input type="hidden" name="client_id" :value="selectedClient ? selectedClient.id : ''">
    </div>
</form>
