<!-- Placeholder: Customize with the correct logic for Maschinenverfügbarkeit -->
<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenverfügbarkeit</h5>
        <canvas id="chart4" onclick="openModal('chart4Modal')"></canvas>
        <p class="card-text" style="color:white;">
            Diese Visualisierung zeigt die Verfügbarkeit der Maschinen über die Zeit.
        </p>
    </div>
</div>

<script>
const chart4 = new Chart(document.getElementById('chart4').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Beispiel1', 'Beispiel2', 'Beispiel3'], 
        datasets: [{
            label: 'Beispiel Daten',
            data: [400, 500, 600], 
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Zeit'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Verfügbarkeit'
                }
            }
        }
    }
});
</script>
