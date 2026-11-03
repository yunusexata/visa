<flux:table container:class="max-h-80">
    <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
        <flux:table.column sticky class="bg-white dark:bg-zinc-900">ID</flux:table.column>
        <flux:table.column>NAMA</flux:table.column>
        <flux:table.column>LPK</flux:table.column>
        <flux:table.column>PAKET</flux:table.column>
        <flux:table.column>TGL BERANGKAT</flux:table.column>
        <flux:table.column>STATUS</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($this->orders as $order)
            <flux:table.row :key="$order->id">
                <flux:table.cell sticky class="bg-white dark:bg-zinc-900 font-medium text-gray-900 dark:text-white">
                    {{ $order->id }}
                </flux:table.cell>

                <flux:table.cell class="font-semibold text-gray-900 dark:text-white">
                    {{ $order->customer_name }}
                </flux:table.cell>

                <flux:table.cell class="text-gray-600 dark:text-zinc-400">
                    {{ $order->lpk }}
                </flux:table.cell>

                <flux:table.cell class="text-gray-600 dark:text-zinc-400">
                    <div class="max-w-[150px] truncate block" title="{{ $order->package }}">
                        {{ $order->package }}
                    </div>
                </flux:table.cell>

                <flux:table.cell class="text-gray-500 dark:text-zinc-400">
                    {{ $order->departure_date }}
                </flux:table.cell>

                <flux:table.cell>
                    <flux:badge variant="pill" size="sm">
                        {{ $order->status }}
                    </flux:badge>
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>