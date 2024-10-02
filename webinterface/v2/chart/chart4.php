<?php
include '../../connection.php';

// Setze den Start- und Endzeitpunkt des 24-Stunden-Zeitraums
$startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
$endDate = date('Y-m-d H:i:s');

// Abrufen der Maschinen und deren Verfügbarkeit innerhalb des 24-Stunden-Zeitraums
$query = "
    SELECT m.idMachine, 
        SUM(TIMESTAMPDIFF(SECOND, GREATEST(s.startTime, '$startDate'), LEAST(IFNULL(s.endTime, '$endDate'), '$endDate'))) AS totalActiveTime
    FROM machine m
    LEFT JOIN shift s ON m.idMachine = s.machine_idMachine
    WHERE s.startTime < '$endDate' 
    AND (s.endTime IS NULL OR s.endTime > '$startDate')
    GROUP BY m.idMachine
    ORDER BY totalActiveTime DESC;  -- Sortiere nach totalActiveTime
";

$result = $machineconn->query($query);

$labels = [];
$activeTimes = [];
$totalActiveTimeAllMachines = 0; // Gesamte aktive Zeit für alle Maschinen

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['idMachine'];
    $activeTime = (int)$row['totalActiveTime'];
    $activeTimes[$row['idMachine']] = $activeTime;
    $totalActiveTimeAllMachines += $activeTime; // Gesamte aktive Zeit summieren
}

// Berechne die Verfügbarkeit in Prozent basierend auf der gesamten aktiven Zeit
$availabilityPercentages = [];
foreach ($activeTimes as $machineId => $activeTime) {
    if ($totalActiveTimeAllMachines > 0) { // Vermeide Division durch Null
        $availabilityPercentage = ($activeTime / $totalActiveTimeAllMachines) * 100;
        $availabilityPercentages[$machineId] = round($availabilityPercentage, 2);
    } else {
        $availabilityPercentages[$machineId] = 0; // Falls keine aktive Zeit vorhanden ist
    }
}

// Sortiere die Labels und Verfügbarkeiten entsprechend der Verfügbarkeit
$sortedAvailability = array_combine($labels, array_values($availabilityPercentages));
arsort($sortedAvailability); // Sortiere absteigend nach Verfügbarkeit

$labels = array_keys($sortedAvailability); // Aktualisierte Labels
$availabilityPercentages = array_values($sortedAvailability); // Aktualisierte Verfügbarkeiten

?>

<div class="card bg-dark" style="min-height: 350px; width: 100%;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenaktivität</h5>
        <canvas id="chart6" style="height: 300px;" onclick="openModal('chart6Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die prozentuale Verfügbarkeit des gesamten Zeitraums an.</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart6Modal" tabindex="-1" aria-labelledby="chart6ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart6ModalLabel">Maschinenaktivität</h5>
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
            label: 'Aktivität der gesamten Produktionszeit',
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
                    text: 'Maschinen-ID'
                },
                ticks: {
                    autoSkip: false
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Aktivität (%)'
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
            label: 'Aktivität der gesamten Produktionszeit',
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
                    text: 'Maschinen-ID'
                },
                ticks: {
                    autoSkip: false
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Aktivität (%)'
                },
                beginAtZero: true,
                max: 100 // Maximalwert auf 100 setzen
            }
        }
    }
});
</script>
