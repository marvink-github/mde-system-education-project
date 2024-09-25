<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maschinendatenerfassung</title>
    <link rel="stylesheet" href="styles.css"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <header>
        <h1 id="page-title"></h1>
        <nav>
            <button onclick="loadPage('device')">Geräte</button>
            <button onclick="loadPage('machine')">Maschinen</button>
            <button onclick="loadPage('shift')">Schichten</button>
            <button onclick="loadPage('machinedata')">Maschinendaten</button>
            <button onclick="loadPage('log')">Logs</button>
        </nav>
    </header>

    <main>
    <button id="toggle-filter">Filter</button>

    <div id="filter-form" style="display: flex; align-items: center; flex-wrap: wrap;">
        <div style="flex-grow: 1; margin-right: 10px;">
            <label for="userid">Benutzer:</label>
            <input type="text" id="userid" name="userid">

            <label for="orderid">Bestellung:</label>
            <input type="text" id="orderid" name="orderid">

            <label for="shiftid">Schicht:</label>
            <input type="text" id="shiftid" name="shiftid">

            <label for="machineid">Maschine:</label>
            <input type="text" id="machineid" name="machineid">
        </div>

        <div style="flex-grow: 1; margin-right: 10px;">
            <label for="timestamp_from">Von:</label>
            <input type="date" id="timestamp_from" name="timestamp_from">

            <label for="timestamp_to">Bis:</label>
            <input type="date" id="timestamp_to" name="timestamp_to">

            <label for="page">Seite:</label>
            <input type="number" id="page" name="page" min="1" value="1">

            <label for="limit">Limit:</label>
            <input type="number" id="limit" name="limit" min="1" value="200">
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <button id="apply-filters">Anwenden</button>
            <button id="reset-filters">Zurücksetzen</button>           
        </div>
    </div>

        <div id="no-data-message" style="color: red;"></div> 

        <section id="dashboard">
            <table id="data-table" class="table table-bordered"> 
                <thead id="table-header">
                    <!-- Header wird dynamisch über JavaScript erzeugt -->
                </thead>
                <tbody id="data-table-body">
                    <!-- Dynamische Daten werden hier eingefügt -->
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 by Marvin Kafka von ananas.codes</p>
    </footer>

    <script src="scripts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
