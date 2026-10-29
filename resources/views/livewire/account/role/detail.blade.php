<form wire:submit="store" class="rounded-sm border border-stroke bg-white p-6 shadow-default dark:border-strokedark dark:bg-boxdark">
    <div class="flex flex-col gap-6">
        
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

        <div class="flex flex-wrap gap-3">
            <button 
                type="button" 
                wire:click="checkAllAccess(1)"
                class="inline-flex items-center justify-center gap-2 rounded bg-primary px-4 py-2 text-center text-sm font-medium text-white hover:bg-opacity-90"
            >
                <i class="ki-duotone ki-check text-lg"></i>
                Check Seluruh
            </button>
            
            <button 
                type="button" 
                wire:click="checkAllAccess(0)"
                class="inline-flex items-center justify-center gap-2 rounded bg-meta-1 px-4 py-2 text-center text-sm font-medium text-white hover:bg-opacity-90"
            >
                <i class="ki-duotone ki-cross text-lg">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                Uncheck Seluruh
            </button>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($accesses as $keyAccess => $access)
                <div wire:key="access_{{ $keyAccess }}" class="rounded-sm border border-stroke bg-white p-5 shadow-default dark:border-strokedark dark:bg-boxdark">
                    
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="font-semibold text-black dark:text-white">
                            {{ $access['name'] }}
                        </div>
                        <div class="flex gap-1.5">
                            <button 
                                type="button" 
                                wire:click="checkAllAccess(1, '{{ $keyAccess }}')"
                                class="inline-flex items-center justify-center gap-1 rounded bg-primary px-2.5 py-1 text-xs font-medium text-white hover:bg-opacity-90"
                            >
                                <i class="ki-duotone ki-check"></i>
                                Check
                            </button>
                            <button 
                                type="button" 
                                wire:click="checkAllAccess(0, '{{ $keyAccess }}')"
                                class="inline-flex items-center justify-center gap-1 rounded bg-meta-1 px-2.5 py-1 text-xs font-medium text-white hover:bg-opacity-90"
                            >
                                <i class="ki-duotone ki-cross">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Uncheck
                            </button>
                        </div>
                    </div>

                    <hr class="my-4 border-stroke dark:border-strokedark" />

                    <div class="flex flex-col gap-3">
                        @foreach ($access['permissions'] as $keyPermission => $permission)
                            <div class="flex items-start">
                                <label for="permission_{{ $keyAccess }}_{{ $keyPermission }}" class="flex cursor-pointer select-none items-center text-sm font-medium text-black dark:text-white">
                                    <div class="relative mt-0.5">
                                        <input 
                                            type="checkbox" 
                                            value="1"
                                            id="permission_{{ $keyAccess }}_{{ $keyPermission }}"
                                            wire:model="accesses.{{ $keyAccess }}.permissions.{{ $keyPermission }}.is_checked"
                                            class="r-3 flex h-5 w-5 items-center justify-center rounded border border-stroke bg-white peer-checked:border-primary peer-checked:bg-primary dark:border-form-strokedark dark:bg-form-input"
                                        />
                                    </div>
                                    <span class="ml-2">{{ $permission['translated_name'] }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endforeach
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