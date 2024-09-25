<?php
include 'header.php';
?>

<main>
<div class="container my-5">
    <div class="row justify-content-center text-center">
        <!-- GET Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card text-center" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#getModal">
                <div class="card-body" style="background-color: green; color: black; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <h1 class="card-title" style="font-size: 48px; font-family: 'Roboto', sans-serif;">GET</h1>
                </div>
            </div>
        </div>

        <!-- POST Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card text-center" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#postModal">
                <div class="card-body" style="background-color: yellow; color: black; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <h1 class="card-title" style="font-size: 48px; font-family: 'Roboto', sans-serif;">POST</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center text-center">
        <!-- PATCH Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card text-center" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#patchModal">
                <div class="card-body" style="background-color: purple; color: black; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <h1 class="card-title" style="font-size: 48px; font-family: 'Roboto', sans-serif;">PATCH</h1>
                </div>
            </div>
        </div>

        <!-- DELETE Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card text-center" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <div class="card-body" style="background-color: red; color: black; height: 200px; display: flex; align-items: center; justify-content: center;">
                    <h1 class="card-title" style="font-size: 48px; font-family: 'Roboto', sans-serif;">DELETE</h1>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal für GET-Anfrage -->
<div class="modal fade" id="getModal" tabindex="-1" aria-labelledby="getModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="getModalLabel">GET-Anfrage</h5>
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

                <button id="fetchDataButton" class="btn btn-primary mt-3">Daten abrufen</button>

                <div id="error-message" class="alert alert-danger mt-3" style="display: none;"></div>
            </div>
            </div>
        </div>
    </div>
</div>


<!-- Container zum Anzeigen der Daten -->
<div id="dataDisplay" class="container my-5" style="display: none;">
    <h2 class="text-center display-4">Maschinendaten</h2>
    <div class="d-flex justify-content-between mb-3">
        <button id="openFilterButton" class="btn btn-info mx-4">Filter öffnen</button>
        <button id="backButton" class="btn btn-secondary mx-4">Zurück zu den Operationen</button>
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


</main>

<?php
include 'footer.php';
?>
