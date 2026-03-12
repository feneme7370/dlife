@props(['id_apexCharts' => '','titles' => [],'data' => [], 'type' => 'bar', 'title' => ''])

<div>
    <div id="{{ $id_apexCharts }}"></div>
</div>

    {{-- public $titles = [2001,1992,1993,1994,1995,1996,1997, 1998,1999];
    public $data = [80,40,35,50,49,60,70,91,125]; --}}

<style>
    .apexcharts-menu{
        background: #18181b !important;
        color: #a1a1aa !important;
        border: 1px solid #3f3f46 !important;
    }

    .apexcharts-menu-item:hover{
        background: #27272a !important;
        color: #fafafa !important;
    }
</style>
<script>
    if({{ $type == 'bar' }}){
        var options = {
        chart: {
            type: "{{ $type }}",
            height: 350,
                toolbar: {
                    color: ['#6366f1'],
                }
        },
        tooltip: {
            theme: 'dark'
        },
        colors: ['#6366f1'], // color principal
        plotOptions: {
            bar: {
            horizontal: true
            }
        },
        series: [{
            name: '{{ $title }}',
            data: @json($data),
            labels: {
                style: {
                    colors: '#6366f1'
                }
            },
        }],
        xaxis: {
            categories: @json($titles),
            labels: {
                style: {
                    colors: '#6366f1'
                }
            },
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#6366f1',
                    fontSize: '12px'
                }
            }
        }
        }
    
        var chart = new ApexCharts(document.querySelector("#{{ $id_apexCharts }}"), options);
    
        chart.render();
    }
</script>