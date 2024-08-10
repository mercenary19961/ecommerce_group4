document.addEventListener('DOMContentLoaded', () => {
  function updateBarChart() {
    fetch('getProducts.php')
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        const productNames = data.map(item => item.name);
        const productCounts = data.map(item => item.count);

        const barChartOptions = {
          series: [{ data: productCounts, name: 'Products' }],
          chart: {
            type: 'bar',
            background: 'transparent',
            height: 350,
            toolbar: { show: false },
          },
          colors: ['#2962ff', '#d50000', '#2e7d32', '#ff6d00', '#583cb3'],
          plotOptions: {
            bar: { distributed: true, borderRadius: 4, horizontal: false, columnWidth: '40%' },
          },
          dataLabels: { enabled: false },
          fill: { opacity: 1 },
          grid: { borderColor: '#55596e', yaxis: { lines: { show: true } }, xaxis: { lines: { show: true } } },
          legend: { labels: { colors: '#000' }, show: true, position: 'top' },
          stroke: { colors: ['transparent'], show: true, width: 2 },
          tooltip: { shared: true, intersect: false, theme: 'dark' },
          xaxis: {
            categories: productNames,
            title: { style: { color: '#000' } },
            axisBorder: { show: true, color: '#55596e' },
            axisTicks: { show: true, color: '#000' },
            labels: { style: { colors: '#000' } },
          },
        };

        const barChart = new ApexCharts(document.querySelector('#bar-chart'), barChartOptions);
        barChart.render();
      })
      .catch(error => console.error('Error fetching the products data:', error));
  }

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
            { name: 'Offers Orders', data: salesOrders },
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
            { opposite: true, title: { text: 'Offers Orders', style: { color: '#000' } }, labels: { style: { colors: ['#000'] } } },
          ],
        };

        const areaChart = new ApexCharts(document.querySelector('#area-chart'), areaChartOptions);
        areaChart.render();
      })
      .catch(error => console.error('Error fetching the orders data:', error));
  }

  updateBarChart();
  updateAreaChart();
});
