<?php
include 'header.php';
?>

<main class="d-flex justify-content-center align-items-center" style="min-height: 50vh;">
    <div class="container text-center">
        <div class="row">
            <div class="col-12 mb-2">
                <button class="btn btn-primary btn-lg w-50" onclick="window.location.href='restapi/device.php'">Geräte</button>
            </div>
            <div class="col-12 mb-2">
                <button class="btn btn-primary btn-lg w-50" onclick="window.location.href='restapi/shift.php'">Schichten</button>
            </div>
            <div class="col-12 mb-2">
                <button class="btn btn-primary btn-lg w-50" onclick="window.location.href='restapi/log.php'">Logs</button>
            </div>
            <div class="col-12 mb-2">
                <button class="btn btn-primary btn-lg w-50" onclick="window.location.href='restapi/machinedata.php'">Maschinendaten</button>
            </div>
            <div class="col-12 mb-2">
                <button class="btn btn-primary btn-lg w-50" onclick="window.location.href='restapi/machine.php'">Maschine</button>
            </div>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
