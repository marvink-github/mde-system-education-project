<?php
include '../../connection.php';

// SQL-Abfrage, um die Summe der Stückzahlen pro Bestellung zu ermitteln und nach Stückzahl absteigend zu sortieren
$query = "SELECT `order`, SUM(value) AS piece_count FROM machinedata GROUP BY `order` ORDER BY piece_count DESC";
$result = $machineconn->query($query);

$labels = [];
$pieceCounts = [];

// Daten in Arrays für die Visualisierung speichern
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['order'];
    $pieceCounts[] = $row['piece_count'];
}
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Bestellungskontrolle</h5>
        <canvas id="chart4" style="height: 300px;" onclick="openModal('chart4Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Summe, die pro Bestellung gefertigt wurden.</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart4Modal" tabindex="-1" aria-labelledby="chart4ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart4ModalLabel">Bestellungskontrolle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart4"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Diagramm für die Stückzahl pro Bestellung
const chart4 = new Chart(document.getElementById('chart4').getContext('2d'), {
    type: 'bar', // Typ des Diagramms auf 'horizontalBar' ändern
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
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
                    text: 'Stückzahl'
                },
                beginAtZero: true
            },
            y: {
                title: {
                    display: true,
                    text: 'Bestellungen'
                },
                ticks: {
                    autoSkip: false // Alle Bestellungen auf der y-Achse anzeigen
                }
            }
        }
    }
});

// Vergrößerte Version für chart4
const enlargedChart4 = new Chart(document.getElementById('enlargedChart4').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
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
                    text: 'Stückzahl'
                },
                beginAtZero: true
            },
            y: {
                title: {
                    display: true,
                    text: 'Bestellungen'
                },
                ticks: {
                    autoSkip: false
                }
            }
        }
    }
});
</script>
