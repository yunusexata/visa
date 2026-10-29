<div class="{{ $containerClass }}" style="{{ $containerStyle }}">
    @yield('content-before')
    <canvas wire:ignore id="{{ $canvasId }}"></canvas>
    @yield('content-after')

    @if (isset($showTable) && $showTable)
        <div class="table-responsive mt-3">
            <table class="table table-bordered text-nowrap w-100 h-100">
                <tr>
                    @foreach ($dataTableHeader as $text)
                        <th>{{ $text }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($dataTableRow as $row)
                        @foreach ($row as $col)
                            <td>{{ $col }}</td>
                        @endforeach
                    @endforeach
                </tr>
            </table>
        </div>
    @endif
</div>

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvasElement = document.getElementById('{{ $canvasId }}');
            const config = @json($config);
            const chartInstance = new Chart(canvasElement, config);

            Livewire.on('js-chart-update-{{ $canvasId }}', (data) => {
                chartInstance.data.datasets = data.datasets;
                chartInstance.data.labels = data.labels;
                chartInstance.update();
            });
        });
    </script>
@endpush
