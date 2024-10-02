<?php
include '../../connection.php';

$query = "SELECT DATE(timestamp) as date, SUM(value) AS piece_count FROM machinedata GROUP BY DATE(timestamp)";
$result = $machineconn->query($query);

$labels = [];
$pieceCounts = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['date'];
    $pieceCounts[] = $row['piece_count'];
}
?>

<div class="col-12 col-sm-6 col-md-4 d-flex justify-content-center mb-3">
    <div class="card bg-dark" style="min-height: 350px; width: 100%; cursor: pointer;">
        <div class="card-body">
            <h5 class="card-title" style="color:white;">Tagesleistung</h5>
            <canvas id="chart1" style="height: 300px;" onclick="openModal('chart1Modal')"></canvas>
            <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Anzahl der produzierten Teile pro Tag.</p>
        </div>
    </div>
</div>


<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart1Modal" tabindex="-1" aria-labelledby="chart1ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart1ModalLabel">Tagesleistung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart1"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Diagramm für die Stückzahl pro Tag
const chart1 = new Chart(document.getElementById('chart1').getContext('2d'), {
    type: 'line', // Typ des Diagramms auf 'line' ändern
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.5)', // Hintergrundfarbe leicht verändert
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                },
                ticks: {
                    autoSkip: false // Alle Daten auf der x-Achse anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                },
                beginAtZero: true // Beginnt die y-Achse bei 0
            }
        },
        plugins: {
            legend: {
                onClick: (e) => e.stopPropagation() // Verhindert das Klicken auf die Legende
            }
        }
    }
});

// Vergrößerte Version für chart1
const enlargedChart1 = new Chart(document.getElementById('enlargedChart1').getContext('2d'), {
    type: 'line', // Typ des Diagramms auf 'line' ändern
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.5)', // Hintergrundfarbe leicht verändert
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                },
                ticks: {
                    autoSkip: false // Alle Daten auf der x-Achse anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                },
                beginAtZero: true // Beginnt die y-Achse bei 0
            }
        },
        plugins: {
            legend: {
                onClick: (e) => e.stopPropagation() // Verhindert das Klicken auf die Legende
            }
        }
    }
});
</script>
