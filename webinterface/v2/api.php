<?php
include 'header.php';
?>

<main>
<div class="container my-5">
    <div class="row">
        <!-- GET Card -->
        <div class="col-md-6 col-lg-6 d-flex justify-content-center mb-3">
            <div class="card text-white bg-dark mb-3" style="width: 24rem; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#getModal">
                <div class="card-body">
                    <h5 class="card-title">GET</h5>
                    <p class="card-text">Sende eine GET-Anfrage an die API, um Daten abzurufen.</p>
                    <a href="#" class="btn btn-primary">Anwenden</a>
                </div>
            </div>
        </div>

        <!-- POST Card -->
        <div class="col-md-6 col-lg-6 d-flex justify-content-center mb-3">
            <div class="card text-white bg-dark mb-3" style="width: 24rem; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body">
                    <h5 class="card-title">POST</h5>
                    <p class="card-text">Sende eine POST-Anfrage an die API, um Daten zu speichern.</p>
                    <a href="#" class="btn btn-primary">Anwenden</a>
                </div>
            </div>
        </div>

        <!-- PATCH Card -->
        <div class="col-md-6 col-lg-6 d-flex justify-content-center mb-3">
            <div class="card text-white bg-dark mb-3" style="width: 24rem; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#patchModal">
                <div class="card-body">
                    <h5 class="card-title">PATCH</h5>
                    <p class="card-text">Sende eine PATCH-Anfrage, um vorhandene Daten zu aktualisieren.</p>
                    <a href="#" class="btn btn-primary">Anwenden</a>
                </div>
            </div>
        </div>

       <!-- DELETE Card -->
        <div class="col-md-6 col-lg-6 d-flex justify-content-center mb-3">
            <div class="card text-white bg-dark mb-3" style="width: 24rem; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <div class="card-body">
                    <h5 class="card-title">DELETE</h5>
                    <p class="card-text">Sende eine DELETE-Anfrage, um Daten zu löschen.</p>
                    <a href="#" class="btn btn-primary" id="openDeleteModalButton">Anwenden</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- GET Modal -->
<div class="modal fade" id="getModal" tabindex="-1" aria-labelledby="getModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="getModalLabel">GET</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Auswahl des Endpunkts -->
                <select id="endpoint-select" class="form-select">
                    <option value="getMachinedata">Maschinendaten</option>
                </select>

                <!-- Filterfelder für die GET-Anfrage -->
                <input type="text" id="userid-input" class="form-control mt-3" placeholder="Benutzer-ID (optional)">
                <input type="text" id="orderid-input" class="form-control mt-3" placeholder="Bestell-ID (optional)">
                <input type="text" id="shiftid-input" class="form-control mt-3" placeholder="Schicht-ID (optional)">
                <input type="text" id="machineid-input" class="form-control mt-3" placeholder="Maschinen-ID (optional)">

                <!-- Datumsfilter -->
                <label for="from-date" class="mt-3">Von (Datum):</label>
                <input type="date" id="from-date" class="form-control">
                <label for="to-date" class="mt-3">Bis (Datum):</label>
                <input type="date" id="to-date" class="form-control">

                <!-- Paginierung -->
                <input type="number" id="page-input" class="form-control mt-3" placeholder="Seite (optional)" value="1">
                <input type="number" id="limit-input" class="form-control mt-3" placeholder="Limit (optional)" value="200">

                <button id="fetchDataButton" class="btn btn-success mt-3">Daten abrufen</button>
            </div>
            </div>
        </div>
    </div>
</div>

<?php // if(isset($_GET['berengar']) && $_GET['berengar'] === 'true'):?>
    <!-- Container zum Anzeigen der Daten -->
    <div id="dataDisplay" class="container my-5" style="display: none;">
        <h2 class="text-center display-4">Maschinendaten</h2>
        <div class="d-flex justify-content-between mb-3">
            <button id="openFilterButton" class="btn btn-info mx-4">Filter öffnen</button>
            <button id="backButton" class="btn btn-secondary mx-4">Zurück</button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Zeitstempel</th>
                        <th scope="col">Benutzer</th>
                        <th scope="col">Wert</th>
                        <th scope="col">Bestellung</th>
                        <th scope="col">SchichtId</th>
                    </tr>
                </thead>
                <tbody id="data-body">
                    <!-- Hier werden die Daten eingefügt -->
                </tbody>
            </table>
        </div>
    </div>    
<?php // endif; ?>

<!-- POST Modal -->
<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalLabel">POST</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="postForm"> 
                    <label for="entityType" class="form-label">Wählen Sie den Typ:</label>
                    <select id="entityType" class="form-select mb-3">
                        <option value="machine">Maschine</option>
                        <option value="device">Gerät</option>
                    </select>

                    <div id="machineFields">
                        <h6>Maschineninformationen:</h6>
                        <input type="text" id="machineName" class="form-control mt-2" placeholder="Maschinenname (erforderlich)" required>
                        <input type="text" id="deviceId" class="form-control mt-2" placeholder="Geräte-ID (erforderlich)" required>
                        <input type="text" id="dEntryStartstop" class="form-control mt-2" placeholder="Digital Eingang Start/Stopp (optional)">
                        <input type="text" id="dEntryCounter" class="form-control mt-2" placeholder="Digital Eingang Zähler (optional)">
                    </div>

                    <button type="submit" id="submitPostButton" class="btn btn-success mt-3">Daten senden</button>
                </form> 
            </div>
        </div>
    </div>
</div>

<!-- PATCH Modal -->
<div class="modal fade" id="patchModal" tabindex="-1" aria-labelledby="patchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patchModalLabel">PATCH</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="machineId" class="form-control mb-3" placeholder="Maschinen-ID (erforderlich)" required>
                <input type="text" id="updatedUserId" class="form-control mb-3" placeholder="Benutzer (optional)">
                <input type="text" id="updatedOrderId" class="form-control mb-3" placeholder="Bestellung (optional)">
                <input type="text" id="updatedName" class="form-control mb-3" placeholder="Maschinenname (optional)">
                <input type="text" id="updatedState" class="form-control mb-3" placeholder="Status (optional)">
                <input type="text" id="updatedDEntryStartstop" class="form-control mb-3" placeholder="Digital Eingang Start/Stopp (optional)">
                <input type="text" id="updatedDEntryCounter" class="form-control mb-3" placeholder="Digital Eingang Zähler (optional)">
                <input type="text" id="updatedDeviceId" class="form-control mb-3" placeholder="Geräte-ID (optional)">
                
                <button type="button" id="submitPatchButton" class="btn btn-success mt-3">Daten aktualisieren</button>
            </div>
        </div>
    </div>
</div>

<!-- DELETE Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">DELETE</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="machineid" class="form-label">Wählen Sie den Typ:</label>
                    <select id="machineid" class="form-select mb-3">
                        <option value="machine">Maschine</option>
                        <option value="device">Gerät</option>
                    </select>
                <input type="text" id="deleteMachineId" class="form-control mb-3" placeholder="ID (erforderlich)" required>
                <button type="button" id="submitDeleteButton" class="btn btn-danger mt-3">Daten löschen</button>
            </div>
        </div>
    </div>
</div>


</main>

<?php
include 'footer.php';
?>
