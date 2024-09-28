<div class="card bg-dark" style="min-height: 350px; margin: 15px 15px 15px 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenverfügbarkeit</h5>
        <canvas id="chart4" onclick="openModal('chart4Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Verfügbarkeit der Maschinen über die Zeit.</p>
    </div>
</div>

<script>
// Beispiel-Daten, die durch echte Daten ersetzt werden können.
const chart4 = new Chart(document.getElementById('chart4').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [{
            label: 'Beispiel',
            data: [368, 152, 521, 745, 870],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: { title: { display: true, text: 'Beispiel' } },
            y: { title: { display: true, text: 'Beispiel' } }
        }
    }
});
</script>
