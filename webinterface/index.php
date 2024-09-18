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
        <h1 id="page-title">Maschinendaten</h1>
        <nav>
            <button onclick="loadData('device')">Geräte</button>
            <button onclick="loadData('machine')">Maschinen</button>
            <button onclick="loadData('shift')">Schichten</button>
            <button onclick="loadData('machinedata')">Maschinendaten</button>
            <button onclick="loadData('log')">Logs</button>
        </nav>
    </header>

    <main>
        <div id="filter-form">
            <label for="userid">Benutzer:</label>
            <input type="text" id="userid" name="userid">
            
            <label for="orderid">Bestellung:</label>
            <input type="text" id="orderid" name="orderid">

            <button id="apply-filters">Filter anwenden</button>
        </div>
        <section id="dashboard">
            <table>
                <thead id="table-header">
                    <tr>
                        <th>ID</th>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Value</th>
                        <th>Order</th>
                        <th>Shift</th>
                    </tr>
                </thead>
                <tbody id="data-table-body">
                </tbody>
            </table>
        </section>
    </main>

    <footer>
        <p>&copy; 2024 ananas.codes</p>
    </footer>

    <script src="scripts.js"></script>
</body>
</html>
