@props(['id_apexCharts' => '','titles' => [],'data' => [], 'type' => 'pie'])

<div>
    <div id="{{ $id_apexCharts }}"></div>
</div>

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
    if({{ $type == 'pie' }}){
        var options = {
            chart: {
                type: "{{ $type }}",
                height: 350
            },
            tooltip: {
                theme: 'dark'
            },
            series: @json($data),
        
            labels: @json($titles),

        },
        
    
        var chart = new ApexCharts(document.querySelector("#{{ $id_apexCharts }}"), options);
    
        chart.render();
    }
</script>