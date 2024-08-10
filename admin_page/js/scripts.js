document.addEventListener('DOMContentLoaded', () => {
  function updateAreaChart() {
    fetch('getOrders.php')
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        const { purchaseOrders, salesOrders, categories } = data;

        const areaChartOptions = {
          series: [
            { name: 'Purchase Orders', data: purchaseOrders },
            { name: 'Sales Orders', data: salesOrders },
          ],
          chart: {
            type: 'area',
            background: 'transparent',
            height: 350,
            stacked: false,
            toolbar: { show: false },
          },
          colors: ['#00ab57', '#d50000'],
          labels: categories,
          dataLabels: { enabled: false },
          fill: { gradient: { opacityFrom: 0.4, opacityTo: 0.1 } },
          grid: { borderColor: '#55596e', yaxis: { lines: { show: true } }, xaxis: { lines: { show: true } } },
          legend: { labels: { colors: '#000' }, show: true, position: 'top' },
          markers: { size: 6, strokeColors: '#ffffff', strokeWidth: 2 },
          stroke: { curve: 'smooth' },
          xaxis: {
            title: { style: { color: '#000' } },
            axisBorder: { show: true, color: '#55596e' },
            axisTicks: { show: true, color: '#55596e' },
            labels: { style: { colors: '#000' } },
          },
          yaxis: [
            { title: { text: 'Purchase Orders', style: { color: '#000' } }, labels: { style: { colors: ['#000'] } } },
            { opposite: true, title: { text: 'Sales Orders', style: { color: '#000' } }, labels: { style: { colors: ['#000'] } } },
          ],
        };

        const areaChart = new ApexCharts(document.querySelector('#area-chart'), areaChartOptions);
        areaChart.render();
      })
      .catch(error => console.error('Error fetching the orders data:', error));
  }

  updateAreaChart();
});
