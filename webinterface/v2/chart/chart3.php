<?php
include '../../connection.php';

// SQL-Abfrage, um die Stückzahlen pro Tag und Maschine zu ermitteln
$query = "
    SELECT 
        DATE(shift.startTime) AS date,
        machine.idMachine AS machine_id,
        SUM(machinedata.value) AS piece_count
    FROM machinedata
    JOIN shift ON machinedata.shift_idshift = shift.idshift
    JOIN machine ON shift.machine_idMachine = machine.idMachine
    WHERE shift.startTime BETWEEN '2024-09-30' AND '2024-10-11'
    GROUP BY date, machine.idMachine
    ORDER BY date ASC";
    
$result = $machineconn->query($query);

$dates = [];
$machineData = [];
$machineIds = [];

while ($row = $result->fetch_assoc()) {
    $dates[] = $row['date'];
    $machineId = "Maschine " . $row['machine_id'];
    
    if (!isset($machineData[$machineId])) {
        $machineData[$machineId] = [];
    }
    
    $machineData[$machineId][] = $row['piece_count'];
    if (!in_array($row['machine_id'], $machineIds)) {
        $machineIds[] = $machineId;
    }
}

// Array für Labels (einzigartige Daten)
$uniqueDates = array_values(array_unique($dates));
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Maschinenproduktivität</h5>
        <canvas id="chart3" style="height: 300px;" onclick="openModal('chart3Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die Anzahl pro Maschine über einen Zeitraum.</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart3Modal" tabindex="-1" aria-labelledby="chart3ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart3ModalLabel">Maschinenproduktivität</h5>
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

// Labels (Daten) und Datensätze für die Maschinenproduktion
const labels = <?php echo json_encode($uniqueDates); ?>;
const machineData = <?php echo json_encode($machineData); ?>;
const datasets = [];

// Farben für jede Maschine definieren
const colors = [
    'rgba(255, 99, 132, 0.5)',   // Maschine 1 - rot
    'rgba(54, 162, 235, 0.5)',   // Maschine 2 - blau
    'rgba(75, 192, 192, 0.5)',   // Maschine 3 - türkis
    'rgba(153, 102, 255, 0.5)',  // Maschine 4 - lila
    'rgba(255, 159, 64, 0.5)',   // Maschine 5 - orange
    'rgba(255, 205, 86, 0.5)'    // Maschine 6 - gelb
];

// Für jede Maschine einen Datensatz erstellen
let colorIndex = 0;
for (let machine in machineData) {
    datasets.push({
        label: machine,
        data: machineData[machine],
        backgroundColor: colors[colorIndex % colors.length],  
        borderColor: colors[colorIndex % colors.length].replace('0.5', '1'),
        borderWidth: 1,
        fill: false
    });
    colorIndex++;
}

// Diagramm für die Maschinenproduktion
const chart3 = new Chart(document.getElementById('chart3').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: datasets
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                },
                ticks: {
                    autoSkip: false // Alle Daten anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                },
                beginAtZero: true // y-Achse bei 0 starten
            }
        },
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
});

// Vergrößerte Version für chart3
const enlargedChart3 = new Chart(document.getElementById('enlargedChart3').getContext('2d'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: datasets
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                },
                ticks: {
                    autoSkip: false // Alle Daten anzeigen
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                },
                beginAtZero: true
            }
        },
        plugins: {
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
});
</script>
