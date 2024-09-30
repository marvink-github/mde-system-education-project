<?php
include '../../connection.php';

// SQL-Abfrage, um die Summe der Stückzahlen pro Benutzer-ID zu ermitteln und nach Stückzahl absteigend zu sortieren
$query = "SELECT userId, SUM(value) AS piece_count FROM machinedata GROUP BY userId ORDER BY piece_count DESC";
$result = $machineconn->query($query);

$labels = [];
$pieceCounts = [];

// Daten in Arrays für die Visualisierung speichern
while ($row = $result->fetch_assoc()) {
    $labels[] = $row['userId'];
    $pieceCounts[] = $row['piece_count'];
}
?>

<div class="card bg-dark" style="min-height: 350px; margin: 15px;">
    <div class="card-body">
        <h5 class="card-title" style="color:white;">Mitarbeiterleistung</h5>
        <canvas id="chart2" style="height: 300px;" onclick="openModal('chart2Modal')"></canvas>
        <p class="card-text" style="color:white;">Diese Visualisierung zeigt die produzierten Teile, sortiert nach Leistung.</p>
    </div>
</div>

<!-- Modal für das vergrößerte Diagramm -->
<div class="modal fade" id="chart2Modal" tabindex="-1" aria-labelledby="chart2ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart2ModalLabel">Mitarbeiterleistung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart2"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Diagramm für die Benutzerleistung
const chart2 = new Chart(document.getElementById('chart2').getContext('2d'), {
    type: 'bar', // Typ des Diagramms auf 'bar' ändern, um die Benutzerleistung darzustellen
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: { title: { display: true, text: 'Benutzer' }},
            y: { title: { display: true, text: 'Stückzahl' }}
        }
    }
});

// Vergrößerte Version für chart2
const enlargedChart2 = new Chart(document.getElementById('enlargedChart2').getContext('2d'), {
    type: 'bar', // Typ des Diagramms auf 'bar' ändern
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($pieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: { title: { display: true, text: 'Benutzer' }},
            y: { title: { display: true, text: 'Stückzahl' }}
        }
    }
});
</script>
