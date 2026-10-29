<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

trait WithChartJs
{
    public $canvasId = "canvas-id";
    public $containerStyle = "";
    public $containerClass = "";

    public $config;

    // public $labels = ['A', 'B', 'C', 'D'];
    // public $dataset = [
    //     [
    //         'label' => 'Dataset 1',
    //         'data' => [100, 200, 300, 400],
    //         'borderColor' => '#fcba03',
    //     ],
    //     [
    //         'label' => 'Dataset 2',
    //         'data' => [400, 300, 200, 100],
    //         'borderColor' => '#0f03fc',
    //     ]
    // ];
    // public $config = [
    //     'type' => 'line',
    //     'options' => [
    //         'responsive' => true,
    //         'plugins' => [
    //             'legend' => [
    //                 'position' => 'top',
    //             ],
    //             'title' => [
    //                 'display' => true,
    //                 'text' => 'Chart.js Line Chart'
    //             ]
    //         ]
    //     ]
    // ];

    abstract public function getConfig(): array;
    abstract public function getData(): array;
    abstract public function getView(): string;
    public function onMount() {}

    public function mount()
    {
        $this->onMount();
        $this->setup();
    }

    public function setup()
    {
        $config = $this->getConfig();
        $dataset = $this->getData();

        $this->config = $config;
        $this->config['data']['labels'] = $dataset['labels'];
        $this->config['data']['datasets'] = $dataset['datasets'];
    }

    public function jsChartUpdate()
    {
        $this->dispatch('js-chart-update', labels: $this->config['data']['labels'], datasets: $this->config['data']['datasets']);
    }

    #[On('chart-add-filter')]
    public function chartAddFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }

        $this->setup();
        $this->jsChartUpdate();
    }

    #[On('chart-refresh')]
    public function chartRefresh()
    {
        $this->refresh();
    }

    public function render()
    {
        return view($this->getView());
    }
}
