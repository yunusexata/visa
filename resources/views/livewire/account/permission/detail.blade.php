<form wire:submit="store" class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        
        <div>
            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                Nama
            </label>
            <input 
                type="text" 
                wire:model.blur="name" 
                class="w-full rounded border bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:bg-form-input dark:text-white
                @error('name') border-meta-1 focus:border-meta-1 active:border-meta-1 dark:border-meta-1 @else border-stroke dark:border-form-strokedark @enderror" 
            />

            @error('name')
                <span class="mt-1.5 block text-sm text-meta-1">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div>
            <label class="mb-2.5 block text-sm font-medium text-black dark:text-white">
                Tipe
            </label>
            <div class="relative z-20 bg-transparent dark:bg-form-input">
                <select 
                    wire:model.blur="type" 
                    class="relative z-20 w-full appearance-none rounded border bg-transparent px-5 py-3 text-black outline-none transition focus:border-primary active:border-primary dark:text-white
                    @error('type') border-meta-1 focus:border-meta-1 active:border-meta-1 dark:border-meta-1 @else border-stroke dark:border-form-strokedark @enderror"
                >
                    <option value="" class="text-body dark:bg-boxdark">Pilih tipe...</option>
                    @foreach (PermissionHelper::TRANSLATE_TYPE as $key => $val)
                        <option value="{{ $key }}">{{ $val }}</option>
                    @endforeach
                </select>
                
                <span class="absolute right-4 top-1/2 z-30 -translate-y-1/2 text-body">
                    <svg class="fill-current" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g opacity="0.8">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M5.29289 8.29289C5.68342 7.90237 6.31658 7.90237 6.70711 8.29289L12 13.5858L17.2929 8.29289C17.6834 7.90237 18.3166 7.90237 18.7071 8.29289C19.0976 8.68342 19.0976 9.31658 18.7071 9.70711L12.7071 15.7071C12.3166 16.0976 11.6834 16.0976 11.2929 15.7071L5.29289 9.70711C4.90237 9.31658 4.90237 8.68342 5.29289 8.29289Z" fill=""></path>
                        </g>
                    </svg>
                </span>
            </div>

            @error('type')
                <span class="mt-1.5 block text-sm text-meta-1">
                    {{ $message }}
                </span>
            @enderror
        </div>
        
    </div>

    <div class="mt-6 flex justify-end">
        <button 
            type="submit" 
            class="inline-flex items-center justify-center gap-2.5 rounded bg-meta-3 px-6 py-3 text-center bg-blue-500 rounded font-medium text-white hover:bg-opacity-90 lg:px-8"
        >
            Save
        </button>
    </div>
</form>