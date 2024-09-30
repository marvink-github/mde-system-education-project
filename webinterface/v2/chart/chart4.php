<?php
include '../../connection.php';

$startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endDate = date('Y-m-d H:i:s');

// Abrufen der Maschinen und deren Verfügbarkeit
$query = "
    SELECT m.idMachine, 
           SUM(TIMESTAMPDIFF(SECOND, s.startTime, IFNULL(s.endTime, NOW()))) AS totalActiveTime
    FROM machine m
    LEFT JOIN shift s ON m.idMachine = s.machine_idMachine AND s.startTime <= '$endDate' 
        AND (s.endTime IS NULL OR s.endTime >= '$startDate')
    GROUP BY m.idMachine
";

$result = $machineconn->query($query);

$labels = [];
$availabilityPercentages = [];
$totalTime = 24 * 60 * 60; // Gesamtzeit in Sekunden für 24 Stunden

while ($row = $result->fetch_assoc()) {
    $labels[] = 'Maschine ' . $row['idMachine'];
    // Berechnung der Verfügbarkeit in Prozent und dann mit 2 multiplizieren
    $availabilityPercentage = ($row['totalActiveTime'] / $totalTime) * 100;
    $adjustedAvailabilityPercentage = min(round($availabilityPercentage * 2, 2), 100); // sicherstellen, dass es nicht über 100% geht
    $availabilityPercentages[] = $adjustedAvailabilityPercentage;
}

?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenverfügbarkeit</h5>
        <canvas id="chart6" style="height: 300px;" onclick="openModal('chart6Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die prozentuale Verfügbarkeit des gesamten Zeitraums an (angepasst).</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart6Modal" tabindex="-1" aria-labelledby="chart6ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart6ModalLabel">Maschinenverfügbarkeit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart6"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Diagramm für die Maschinenverfügbarkeit
const chart6 = new Chart(document.getElementById('chart6').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Verfügbarkeit (%) (Insgesamt)',
            data: <?php echo json_encode($availabilityPercentages); ?>,
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
                    autoSkip: false
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Verfügbarkeit (%)'
                },
                beginAtZero: true,
                max: 100 // Maximalwert auf 100 setzen
            }
        }
    }
});

// Vergrößerte Version für chart6
const enlargedChart6 = new Chart(document.getElementById('enlargedChart6').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Verfügbarkeit (%) (angepasst)',
            data: <?php echo json_encode($availabilityPercentages); ?>,
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
                    autoSkip: false
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Verfügbarkeit (%)'
                },
                beginAtZero: true,
                max: 100 // Maximalwert auf 100 setzen
            }
        }
    }
});
</script>
