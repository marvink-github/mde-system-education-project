<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maschinendaten</title>
    <link rel="stylesheet" href="styles.css"> 
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
        <div id="filter-form" style="display: flex; align-items: center;">
            <div style="flex-grow: 1;">
                <label for="userid">Benutzer:</label>
                <input type="text" id="userid" name="userid">
                
                <label for="orderid">Bestellung:</label>
                <input type="text" id="orderid" name="orderid">

                <label for="shiftid">Schicht:</label>
                <input type="text" id="shiftid" name="shiftid">

                <label for="machineid">Maschine:</label>
                <input type="text" id="machineid" name="machineid">

                <button id="apply-filters">Anwenden</button>
                <button id="reset-filters">Zurücksetzen</button>
            </div>

            <div style="display: flex; align-items: center; margin-left: 10px;">
                <span id="record-count" style="margin-left: 10px;"></span>
            </div>
        </div>

        <div id="no-data-message" style="color: red;"></div> 

        <section id="dashboard">
            <table id="data-table"> 
                <thead id="table-header">
                    <!-- Der Header wird dynamisch über JavaScript erzeugt -->
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
</body>
</html>
