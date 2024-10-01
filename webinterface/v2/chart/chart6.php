<?php
include '../../connection.php';

// Abfrage zum Ermitteln des `last_alive` Zeitstempels für das Gerät
$query = "SELECT last_alive FROM device WHERE idDevice = 1"; 
$result = $machineconn->query($query);
$lastAliveTimestamp = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $lastAliveTimestamp = $row['last_alive'];
}
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Terminalaktivität</h5>
        <canvas id="chart5" style="height: 300px;" onclick="openModal('chart5Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Aktivität basierend auf dem Aktivitätszeitstempel.</p>
    </div>
</div>

<!-- Modal für das erweiterte Diagramm -->
<div class="modal fade" id="chart5Modal" tabindex="-1" aria-labelledby="chart5ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart5ModalLabel">Erweiterte Terminalaktivität</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart5"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Modal-Öffnungsfunktion
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Daten und Logik für das Hauptdiagramm mit Legende im Ring
const lastAlive = new Date("<?php echo $lastAliveTimestamp; ?>");
const now = new Date();
let backgroundColor, statusText;

const diff = (now - lastAlive) / 1000; // Unterschied in Sekunden

if (diff > 86400) { 
    backgroundColor = 'rgba(255, 99, 132, 1)'; // Rot
    statusText = 'Offline > 24h';
} else if (diff > 3600) {
    backgroundColor = 'rgba(255, 206, 86, 1)'; // Gelb
    statusText = 'Offline > 1h';
} else {
    backgroundColor = 'rgba(75, 192, 192, 1)'; // Grün
    statusText = 'Online < 1h';
}

// Plugin für die Legende im Ring
const legendInRingPlugin = {
    id: 'legendInRing',
    afterDraw: function(chart) {
        if (chart.canvas.id === 'chart5') {
            const ctx = chart.ctx;
            const width = chart.width;
            const height = chart.height;
            ctx.restore();
            const fontSize = (height / 150).toFixed(2);
            ctx.font = fontSize + "em Arial";
            ctx.textBaseline = "middle";

            const text = statusText;
            const textX = Math.round((width - ctx.measureText(text).width) / 2);
            const textY = height / 2;

            ctx.fillStyle = 'white'; 
            ctx.fillText(text, textX, textY);
            ctx.save();
        }
    }
};

// Erstellen des Hauptdiagramms (Doughnut)
const chart5 = new Chart(document.getElementById('chart5').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: ['Online < 1h', 'Offline > 1h', 'Offline > 24h'],
        datasets: [{
            label: 'Aktivität',
            data: [1],
            backgroundColor: [backgroundColor],
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        plugins: {
            legend: {
                display: false 
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true,
            duration: 1500
        }
    },
    plugins: [legendInRingPlugin]
});


// Ruft die updateEnlargedChart-Funktion auf, wenn das Modal geöffnet wird
document.getElementById('chart5Modal').addEventListener('show.bs.modal', updateEnlargedChart);
</script>
