<?php 
include '../../connection.php';

// Abfrage zum Ermitteln der `last_alive` Zeitstempel für das Gerät und andere Details
$query = "SELECT last_alive, idDevice, terminal_type FROM device WHERE idDevice = 1"; 
$result = $machineconn->query($query);
$lastAliveTimestamp = null;
$terminalId = null;
$terminalType = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastAliveTimestamp = $row['last_alive'];
    $terminalId = $row['idDevice']; // Terminal-ID speichern
    $terminalType = $row['terminal_type'];
}
?>

<div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center">
    <div class="card bg-dark" style="min-height: 350px; width: 100%; cursor: pointer;">
        <div class="card-body">
            <h5 class="card-title" style="color:white;">Terminalaktivität</h5>
            <canvas id="chart5" style="height: 300px;"></canvas>
            <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Aktivität basierend auf dem Aktivitätszeitstempel.</p>
        </div>
    </div>
</div>


<script>
// Daten und Logik für das Hauptdiagramm mit Legende im Ring
const lastAlive = new Date("<?php echo $lastAliveTimestamp; ?>");
const now = new Date();
let onlineTime = 0, maintenanceTime = 0, offlineTime = 0;

const diff = (now - lastAlive) / 1000; // Unterschied in Sekunden

// Berechnung der Zeiten
if (diff <= 3600) { // Online: letzte Stunde
    onlineTime = diff;
} else if (diff <= 86400) { // Wartung: 1 Stunde bis 24 Stunden
    onlineTime = 3600; // Maximal 1 Stunde online
    maintenanceTime = diff - 3600; // Zeit in Wartung
} else { // Offline: mehr als 24 Stunden
    onlineTime = 3600; // Maximal 1 Stunde online
    maintenanceTime = 86400 - 3600; // Zeit in Wartung
    offlineTime = diff - 86400; // Zeit offline
}

// Gesamte Zeit berechnen
const totalTime = onlineTime + maintenanceTime + offlineTime;

// Erstellen des Hauptdiagramms (Doughnut)
const chart5 = new Chart(document.getElementById('chart5').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Online', 'Wartung', 'Offline'],
        datasets: [{
            label: 'Aktivität',
            data: [onlineTime, maintenanceTime, offlineTime],
            backgroundColor: [
                'rgba(75, 192, 192, 1)',  // Grün: Online
                'rgba(255, 206, 86, 1)',  // Gelb: Wartung
                'rgba(255, 99, 132, 1)'    // Rot: Offline
            ],
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1,
            hoverOffset: 20 // Vergrößert den Hover-Bereich
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        radius: '80%',
        plugins: {
            legend: {
                display: true,
                onClick: (e) => e.stopPropagation() // Verhindert das Klicken auf die Legende
            },
            tooltip: {
                enabled: true,
                mode: 'nearest',
                intersect: true,
                animation: false, // Deaktiviert die Animation des Tooltips
                callbacks: {
                    label: function(tooltipItem) {
                        const status = tooltipItem.label;
                        const timeSpent = tooltipItem.raw;
                        const percentage = ((timeSpent / totalTime) * 100).toFixed(2);
                        return `${status}: ${percentage}%`;
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true,
            duration: 1500
        }
    }
});

// Text in der Mitte des Donuts anzeigen
Chart.plugins.register({
    beforeDraw: function(chart) {
        if (chart.config.options.elements.center) {
            const ctx = chart.ctx;
            const txt = `ID: ${<?php echo $terminalId; ?>}`; // Terminal-ID
            const txt2 = 'Aktivität'; // Optionale Beschriftung
            const fontSize = 18;
            ctx.restore();
            ctx.font = fontSize + "px Arial";
            ctx.fillStyle = "white"; // Textfarbe
            ctx.textBaseline = "middle";
            const textX = Math.round((chart.width - ctx.measureText(txt).width) / 2);
            const textY = Math.round(chart.height / 2);
            ctx.fillText(txt, textX, textY);
            ctx.fillText(txt2, textX, textY + 25); // Optionaler Text
            ctx.save();
        }
    }
});
</script>
