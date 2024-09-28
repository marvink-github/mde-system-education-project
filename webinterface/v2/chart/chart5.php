<?php
include '../../connection.php';

// Abfrage zum Ermitteln des last_alive Zeitstempels für die Maschine
$query = "SELECT last_alive FROM device WHERE idDevice = 1"; // Hier die entsprechende ID der Maschine angeben
$result = $machineconn->query($query);
$lastAliveTimestamp = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastAliveTimestamp = $row['last_alive'];
}
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Geräteaktivität</h5>
        <canvas id="chart5" style="height: 300px;"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Aktivität des Geräts basierend auf dem letzten Aktivitätszeitstempel.</p>
    </div>
</div>

<script>
const lastAlive = new Date("<?php echo $lastAliveTimestamp; ?>");
const now = new Date();
let backgroundColor;

// Berechnung des Zeitunterschieds
const diff = (now - lastAlive) / 1000; // Unterschied in Sekunden

// Hintergrundfarbe basierend auf dem Zeitunterschied festlegen
if (diff > 86400) { // 24 Stunden
    backgroundColor = 'rgba(255, 99, 132, 1)'; // Rot
} else if (diff > 3600) { // 1 Stunde
    backgroundColor = 'rgba(255, 206, 86, 1)'; // Gelb
} else {
    backgroundColor = 'rgba(75, 192, 192, 1)'; // Grün
}

// Diagramm für die Geräteaktivität (Kreis)
const chart5 = new Chart(document.getElementById('chart5').getContext('2d'), {
    type: 'doughnut', // Typ auf 'doughnut' ändern, um einen Kreis zu erstellen
    data: {
        labels: ['Aktivität'], // Nur ein Label
        datasets: [{
            label: 'Aktivität',
            data: [1], // Ein Wert für die Aktivität
            backgroundColor: [backgroundColor], // Farbe des Kreises
            borderColor: 'rgba(255, 255, 255, 1)', // Weißer Rand für den Kreis
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false, // Höhe anpassen
        cutout: '80%', // Inneren Teil des Doughnut-Kreises
        plugins: {
            legend: {
                display: false // Legende ausblenden
            }
        }
    }
});

// Funktion zur Aktualisierung des Diagramms
function updateChart() {
    const now = new Date();
    const diff = (now - lastAlive) / 1000;

    if (diff > 86400) {
        backgroundColor = 'rgba(255, 99, 132, 1)'; // Rot
    } else if (diff > 3600) {
        backgroundColor = 'rgba(255, 206, 86, 1)'; // Gelb
    } else {
        backgroundColor = 'rgba(75, 192, 192, 1)'; // Grün
    }

    // Aktualisieren des Diagramms
    chart5.data.datasets[0].backgroundColor[0] = backgroundColor;
    chart5.update();
}

// Intervall zum Aktualisieren des Diagramms jede Minute
setInterval(updateChart, 60000);
</script>
