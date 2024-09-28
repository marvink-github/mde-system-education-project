<?php
include '../../connection.php';

// Abfrage der Maschinenaktivität basierend auf der shift-Tabelle
$query = "
SELECT machine_idMachine, SUM(TIMESTAMPDIFF(MINUTE, startTime, endTime)) AS active_minutes 
FROM shift 
GROUP BY machine_idMachine";
$result = $machineconn->query($query);

$labels = [];
$activeMinutes = [];

// Daten abrufen
while ($row = $result->fetch_assoc()) {
    $labels[] = "Maschine " . $row['machine_idMachine']; // Label für jede Maschine
    $activeMinutes[] = $row['active_minutes']; // Summe der aktiven Minuten
}
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenaktivität</h5>
        <canvas id="chart3" style="height: 300px;" onclick="openModal('chart3Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die aktive Zeit jeder Maschine.</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart3Modal" tabindex="-1" aria-labelledby="chart3ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart3ModalLabel">Maschinenaktivität</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart3"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Diagramm für die Maschinenaktivität
const chart3 = new Chart(document.getElementById('chart3').getContext('2d'), {
    type: 'bar', // Verwendung von Säulen für die Aktivität
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Aktive Minuten',
            data: <?php echo json_encode($activeMinutes); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Maschinen'
                },
                ticks: {
                    autoSkip: false // Alle Maschinen auf der x-Achse anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Aktive Minuten'
                },
                beginAtZero: true // Beginnt die y-Achse bei 0
            }
        }
    }
});

// Vergrößerte Version für chart3
const enlargedChart3 = new Chart(document.getElementById('enlargedChart3').getContext('2d'), {
    type: 'bar', // Verwendung von Säulen für die Aktivität
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Aktive Minuten',
            data: <?php echo json_encode($activeMinutes); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Maschinen'
                },
                ticks: {
                    autoSkip: false // Alle Maschinen auf der x-Achse anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Aktive Minuten'
                },
                beginAtZero: true // Beginnt die y-Achse bei 0
            }
        }
    }
});
</script>
