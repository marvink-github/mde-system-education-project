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

<div class="card bg-dark" style="min-height: 350px; width: 100%;">
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
let backgroundColor;

const diff = (now - lastAlive) / 1000; // Unterschied in Sekunden

if (diff > 86400) { 
    backgroundColor = 'rgba(255, 99, 132, 1)'; // Rot
} else if (diff > 3600) {
    backgroundColor = 'rgba(255, 206, 86, 1)'; // Gelb
} else {
    backgroundColor = 'rgba(75, 192, 192, 1)'; // Grün
}

// Plugin für die Legende im Ring
const legendInRingPlugin = {
    id: 'legendInRing',
    afterDraw: function(chart) {
        // Text im inneren Kreis entfernen
        // Kein Code erforderlich, um Text anzuzeigen, also diese Funktion leer lassen
    },
    tooltip: {
        callbacks: {
            label: function(tooltipItem) {
                // ID des Gerätes für Tooltip anzeigen
                return `Terminal-ID: ${<?php echo $terminalId; ?>}`;
            }
        }
    }
};

// Erstellen des Hauptdiagramms (Doughnut)
const chart5 = new Chart(document.getElementById('chart5').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: [
            'Online', 
            'Wartung', 
            'Offline'
        ],
        datasets: [{
            label: 'Aktivität',
            data: [
                (diff <= 3600 ? 1 : 0), // Online
                (diff > 3600 && diff <= 86400 ? 1 : 0), // Wartung
                (diff > 86400 ? 1 : 0) // Offline
            ],
            backgroundColor: [
                'rgba(75, 192, 192, 1)',  // Grün: Online
                'rgba(255, 206, 86, 1)',  // Orange: Wartung
                'rgba(255, 99, 132, 1)'    // Rot: Offline
            ],
            borderColor: 'rgba(255, 255, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '80%',
        radius: '80%',
        plugins: {
            legend: {
                display: true 
            },
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return `idDevice: ${<?php echo $terminalId; ?>}`;
                    }
                }
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

// Hier wird die Callback-Funktion hinzugefügt
document.getElementById('chart5Modal').addEventListener('show.bs.modal', function () {
    // Logik, die beim Öffnen des Modals ausgeführt werden soll
});
</script>
