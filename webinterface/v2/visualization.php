<?php
include 'header.php';
include '../../connection.php';

$query = "SELECT DATE(timestamp) as date, userid, SUM(value) AS piece_count FROM machinedata GROUP BY DATE(timestamp), userid";
$result = $machineconn->query($query);

$labels = [];
$pieceCounts = [];
$userCounts = [];
$userPieceCounts = [];
$userIds = [];

while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    
    if (!in_array($date, $labels)) {
        $labels[] = $date; 
        $pieceCounts[] = 0; 
    }
    
    $dateIndex = array_search($date, $labels);
    $pieceCounts[$dateIndex] += (int)$row['piece_count']; 

    if (!isset($userCounts[$row['userid']])) {
        $userCounts[$row['userid']] = 0;
        $userIds[] = $row['userid']; 
    }
    $userCounts[$row['userid']] += (int)$row['piece_count'];
}

$userPieceCounts = array_values($userCounts);
?>

<main>
<div class="container" style="padding-top: 25px; padding-bottom: 25px;padding-left: 10px; padding-right: 10px;">
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <div class="col">
            <div class="card bg-dark" style="min-height: 350px; margin: 15px 15px 15px 15px;">
                <div class="card-body">
                    <h5 class="card-title" style="color:white;">Stückzahl pro Tag</h5>
                    <canvas id="chart1" style="height: 300px;" onclick="openModal('chart1Modal')"></canvas>
                    <p class="card-text" style="color:white;">
                        Diese Visualisierung zeigt die Anzahl der produzierten Teile pro Tag.
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card bg-dark" style="min-height: 350px; margin: 15px 15px 15px 15px;">
                <div class="card-body">
                    <h5 class="card-title" style="color:white;">Benutzerleistung</h5>
                    <canvas id="chart2" onclick="openModal('chart2Modal')"></canvas>
                    <p class="card-text" style="color:white;">
                        Diese Visualisierung zeigt die Anzahl der produzierten Stücke pro Benutzer.
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card bg-dark" style="min-height: 350px; margin: 15px 15px 15px 15px;">
                <div class="card-body">
                    <h5 class="card-title" style="color:white;">Maschinenaktivitätsdauer</h5>
                    <canvas id="chart3" onclick="openModal('chart3Modal')"></canvas>
                    <p class="card-text" style="color:white;">
                        Diese Visualisierung zeigt die Aktivitätsdauer der Maschinen über die Zeit.
                    </p>
                </div>
            </div>
        </div>

        <div class="col">
           <div class="card bg-dark" style="min-height: 350px; margin: 15px 15px 15px 15px;">
                <div class="card-body">
                    <h5 class="card-title" style="color:white;">Maschinenverfügbarkeit</h5>
                    <canvas id="chart4" onclick="openModal('chart4Modal')"></canvas>
                    <p class="card-text" style="color:white;">
                        Diese Visualisierung zeigt die Verfügbarkeit der Maschinen über die Zeit.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Modals for enlarged charts -->
<div class="modal fade" id="chart1Modal" tabindex="-1" aria-labelledby="chart1ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart1ModalLabel">Stückzahl pro Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart1"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chart2Modal" tabindex="-1" aria-labelledby="chart2ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart2ModalLabel">Benutzerleistung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart2"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chart3Modal" tabindex="-1" aria-labelledby="chart3ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart3ModalLabel">Maschinenaktivitätsdauer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart3"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="chart4Modal" tabindex="-1" aria-labelledby="chart4ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chart4ModalLabel">Maschinenverfügbarkeit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <canvas id="enlargedChart4"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>

<script>
function openModal(modalId) {
    var myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
}

// Chart for Stückzahl pro Tag
const chart1 = new Chart(document.getElementById('chart1').getContext('2d'), {
    type: 'line',
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
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                }
            }
        }
    }
});

// Enlarged version for chart1
const enlargedChart1 = new Chart(document.getElementById('enlargedChart1').getContext('2d'), {
    type: 'line',
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
            x: {
                title: {
                    display: true,
                    text: 'Datum'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                }
            }
        }
    }
});

// Chart for Benutzerleistung
const chart2 = new Chart(document.getElementById('chart2').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($userIds); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($userPieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Benutzer ID'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                }
            }
        }
    }
});

// Enlarged version for chart2
const enlargedChart2 = new Chart(document.getElementById('enlargedChart2').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($userIds); ?>,
        datasets: [{
            label: 'Stückzahl',
            data: <?php echo json_encode($userPieceCounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Benutzer ID'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Stückzahl'
                }
            }
        }
    }
});

// Chart 3 Placeholder 
const chart3 = new Chart(document.getElementById('chart3').getContext('2d'), {
    type: 'line', 
    data: {
        labels: ['1', '2', '3', '4', '5'], 
        datasets: [{
            label: 'Beispiel', 
            data: [368, 152, 521, 745, 870], 
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            }
        }
    }
});

// Enlarged version for chart3
const enlargedChart3 = new Chart(document.getElementById('enlargedChart3').getContext('2d'), {
    type: 'line', 
    data: {
        labels: ['1', '2', '3', '4', '5'], 
        datasets: [{
            label: 'Beispiel', 
            data: [368, 152, 521, 745, 870], 
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            }
        }
    }
});

// Chart 4 Placeholder (example data)
const chart4 = new Chart(document.getElementById('chart4').getContext('2d'), {
    type: 'bar', 
    data: {
        labels: ['1', '2', '3', '4', '5'], 
        datasets: [{
            label: 'Beispiel', 
            data: [368, 152, 521, 745, 870], 
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            }
        }
    }
});

// Enlarged version for chart4
const enlargedChart4 = new Chart(document.getElementById('enlargedChart4').getContext('2d'), {
    type: 'bar', // Beispieltyp
    data: {
        labels: ['1', '2', '3', '4', '5'], 
        datasets: [{
            label: 'Beispiel', 
            data: [368, 152, 521, 745, 870],
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Beispiel'
                }
            }
        }
    }
});
</script>

