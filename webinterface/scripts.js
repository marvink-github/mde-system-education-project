document.addEventListener("DOMContentLoaded", function () {
    const currentType = localStorage.getItem('currentType') || 'machinedata';
    loadData(currentType);
});

function loadData(type, filters = {}) {
    let endpoint;
    let headers = [];
    
    switch (type) {
        case 'device':
            endpoint = 'http://127.0.0.1/api/api/getDevice';
            headers = ['ID', 'Terminal_ID', 'Terminal_Type', 'Last_Alive'];
            updatePageTitle('Geräte');
            break;
        case 'machine':
            endpoint = buildEndpointWithParams('http://127.0.0.1/api/api/getMachine', filters);
            headers = ['ID', 'Name', 'User', 'Order', 'State', 'd_entry_startstop', 'd_entry_counter', 'Device_ID'];
            updatePageTitle('Maschinen');
            break;
        case 'shift':
            endpoint = 'http://127.0.0.1/api/api/getShift';
            headers = ['ID', 'Startzeit', 'Endzeit', 'Maschinen_ID'];
            updatePageTitle('Schichten');
            break;
        case 'machinedata':
            endpoint = buildEndpointWithParams('http://127.0.0.1/api/api/getMachinedata', filters);
            headers = ['ID', 'Timestamp', 'User', 'Value', 'Order', 'Shift_ID'];
            updatePageTitle('Maschinendaten');
            break;
        case 'log':
            endpoint = 'http://127.0.0.1/api/api/getLog';
            headers = ['ID', 'Timestamp', 'Log_Type', 'Log_Message'];
            updatePageTitle('Logs');
            break;
        default:
            console.error('Unbekannter Typ:', type);
            return;
    }

    updateTableHeader(headers);

    fetch(endpoint, {
        method: 'GET',
        headers: {
            'ApiKey': '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht ok: ' + response.statusText);
        }
        return response.json();
    })
    .then(data => {
        console.log(data);
        displayData(data, headers.length);
    })
    .catch(error => console.error("Fehler beim Abrufen der Daten:", error));
}

function buildEndpointWithParams(baseUrl, filters) {
    const queryParams = new URLSearchParams();
    for (const key in filters) {
        if (filters[key]) {
            queryParams.append(key, filters[key]);
        }
    }
    return `${baseUrl}?${queryParams.toString()}`;
}

function updateTableHeader(headers) {
    const tableHeader = document.getElementById('table-header');
    tableHeader.innerHTML = '';
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        tableHeader.appendChild(th);
    });
}

function displayData(data, numHeaders) {
    const tableBody = document.getElementById('data-table-body');
    tableBody.innerHTML = '';

    if (Array.isArray(data)) {
        data.forEach(item => {
            const row = document.createElement('tr');
            for (let i = 0; i < numHeaders; i++) {
                const cell = document.createElement('td');
                cell.textContent = item[Object.keys(item)[i]] || 'N/A'; 
                row.appendChild(cell);
            }
            tableBody.appendChild(row);
        });
    } else {
        console.error("Die zurückgegebenen Daten sind kein Array:", data);
    }
}

function updatePageTitle(pageName) {
    const titleElement = document.getElementById('page-title');
    titleElement.textContent = pageName;
}
