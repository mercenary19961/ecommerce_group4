<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Request Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <?php $x = '<canvas id="myChart"></canvas>'; ?>

    <script>
        // Fetch the data from the PHP endpoint
        fetch('http://localhost/ecommerce/ecommerce_group4/admin_page/get_Top_order.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Fetched data:', data); // Debugging

                // Ensure data is an array
                if (!Array.isArray(data)) {
                    throw new Error('Data is not an array');
                }

                const labels = data.map(item => item.name);
                const requestCounts = data.map(item => item.request_count);

                // Ensure there are labels and data
                if (labels.length === 0 || requestCounts.length === 0) {
                    throw new Error('No data available');
                }

                const ctx = document.getElementById('myChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar', // You can change this to 'line', 'pie', etc.
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Number of Requests',
                            data: requestCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching the data:', error);
                // Optionally, display an error message to the user
                document.body.innerHTML = '<p>Error loading chart. Please check the console for details.</p>';
            });
    </script>
</body>

</html>