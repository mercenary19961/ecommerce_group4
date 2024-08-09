document.addEventListener('DOMContentLoaded', () => {
  // Fetch products data and update the bar chart
  fetch('getProducts.php')
    .then(response => response.json())
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
        grid: {
          borderColor: '#55596e',
          yaxis: { lines: { show: true } },
          xaxis: { lines: { show: true } },
        },
        legend: {
          labels: { colors: '#000' },
          show: true,
          position: 'top',
        },
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
    });

  // Fetch orders data and update the area chart
  fetch('getOrders.php')
    .then(response => response.json())
    .then(data => {
      const purchaseOrders = data.purchaseOrders;
      const salesOrders = data.salesOrders;
      const categories = data.categories;

      const areaChartOptions = {
        series: [
          { name: 'Cash Orders', data: purchaseOrders },
          { name: 'Credit Orders', data: salesOrders }
        ],
        chart: {
          type: 'area',
          background: 'transparent',
          height: 350,
          stacked: false,
          toolbar: { show: false }
        },
        colors: ['#00E396', '#FEB019'],
        dataLabels: { enabled: false },
        fill: { gradient: { enabled: true, opacityFrom: 0.55, opacityTo: 0.35 } },
        grid: { borderColor: '#55596e' },
        legend: { labels: { colors: '#000' }, show: true, position: 'top' },
        markers: { size: 0 },
        stroke: { curve: 'smooth' },
        xaxis: {
          categories: categories,
          title: { style: { color: '#000' } },
          axisBorder: { show: true, color: '#55596e' },
          axisTicks: { show: true, color: '#000' },
          labels: { style: { colors: '#000' } }
        },
        yaxis: {
          labels: { style: { colors: '#000' } },
          title: { text: 'Orders', style: { color: '#000' } }
        },
        tooltip: { shared: true, intersect: false, theme: 'dark' }
      };

      const areaChart = new ApexCharts(document.querySelector('#area-chart'), areaChartOptions);
      areaChart.render();
    })
    .catch(error => console.error('Error fetching the orders data:', error));
});
