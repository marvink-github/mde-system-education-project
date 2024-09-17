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
            <button onclick="loadData('device')">Device</button>
            <button onclick="loadData('machine')">Maschine</button>
            <button onclick="loadData('shift')">Schicht</button>
            <button onclick="loadData('machinedata')">Maschinendaten</button>
            <button onclick="loadData('log')">Log</button>
        </nav>
    </header>

    <main>
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
