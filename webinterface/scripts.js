function loadData(type, filters = {}) {
    localStorage.setItem('currentType', type);

    let endpoint;
    let headers = [];
    
    switch (type) {
        case 'device':
            endpoint = 'http://127.0.0.1/api/api/getDevice';
            headers = ['ID', 'Geräte_ID', 'Geräte_Typ', 'Zuletzt am Leben', 'Firmware_Version'];
            updatePageTitle('Geräte');
            break;
        case 'machine':
            endpoint = buildEndpointWithParams('http://127.0.0.1/api/api/getMachine', filters);
            headers = ['ID', 'Name', 'Benutzer', 'Bestellung', 'Status', 'D_Eingang Start/Stopp', 'D_Eingang Zähler', 'GerätID'];
            updatePageTitle('Maschinen');
            break;
        case 'shift':
            endpoint = 'http://127.0.0.1/api/api/getShift';
            headers = ['ID', 'Startzeit', 'Endzeit', 'Maschineid'];
            updatePageTitle('Schichten');
            break;
        case 'machinedata':
            endpoint = buildEndpointWithParams('http://127.0.0.1/api/api/getMachinedata', filters);
            headers = ['ID', 'Zeitstempel', 'Benutzer', 'Wert', 'Bestellung', 'Schichtid'];
            updatePageTitle('Maschinendaten');
            break;
        case 'log':
            endpoint = 'http://127.0.0.1/api/api/getLog';
            headers = ['ID', 'Zeitstempel', 'Typ', 'Nachricht'];
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
        updateRecordCount(data.length);
        if (data.length === 0) {
            document.getElementById('no-data-message').textContent = 'Keine Einträge gefunden.'; 
            document.getElementById('data-table').style.display = 'none'; 
        } else {
            document.getElementById('no-data-message').textContent = ''; 
            document.getElementById('data-table').style.display = 'table'; 
            displayData(data, headers.length); 
        }
    })
    .catch(error => console.error("Fehler beim Abrufen der Daten:", error));
}



document.getElementById('apply-filters').addEventListener('click', function () {
    const userId = document.getElementById('userid').value;
    const orderId = document.getElementById('orderid').value;
    const shiftId = document.getElementById('shiftid').value;
    const machineId = document.getElementById('machineid').value;

    localStorage.setItem('filter_userid', userId);
    localStorage.setItem('filter_orderid', orderId);
    localStorage.setItem('filter_shiftid', shiftId);
    localStorage.setItem('filter_machineid', machineId);

    const filters = {};
    if (userId) filters.userid = userId;
    if (orderId) filters.orderid = orderId;
    if (shiftId) filters.shiftid = shiftId;
    if (machineId) filters.machineid = machineId;

    loadData('machinedata', filters); 

    location.reload();
});


document.getElementById('reset-filters').addEventListener('click', function() {
    localStorage.removeItem('filter_userid');
    localStorage.removeItem('filter_orderid');
    localStorage.removeItem('filter_shiftid');
    localStorage.removeItem('filter_machineid');

    document.getElementById('userid').value = '';
    document.getElementById('orderid').value = '';
    document.getElementById('shiftid').value = '';
    document.getElementById('machineid').value = '';

    loadData('machinedata', {}); 
});


function loadPage(page) {
    localStorage.setItem('currentPage', page);
    
    if (page === 'machinedata') {
        const savedFilters = {
            userid: localStorage.getItem('filter_userid') || '',
            orderid: localStorage.getItem('filter_orderid') || '',
            shiftid: localStorage.getItem('filter_shiftid') || '',
            machineid: localStorage.getItem('filter_machineid') || ''
        };

        loadData(page, savedFilters);
    } else {
        loadData(page);
    }
}


window.onload = function() {
    const currentPage = localStorage.getItem('currentPage') || 'machinedata'; 

    if (currentPage === 'machinedata') {
        const savedFilters = {
            userid: localStorage.getItem('filter_userid') || '',
            orderid: localStorage.getItem('filter_orderid') || '',
            shiftid: localStorage.getItem('filter_shiftid') || '',
            machineid: localStorage.getItem('filter_machineid') || ''
        };

        document.getElementById('userid').value = savedFilters.userid;
        document.getElementById('orderid').value = savedFilters.orderid;
        document.getElementById('shiftid').value = savedFilters.shiftid;
        document.getElementById('machineid').value = savedFilters.machineid;

        loadData(currentPage, savedFilters); 
    } else {
        loadData(currentPage);
    }
};

window.addEventListener('load', function () {
    document.getElementById('userid').value = localStorage.getItem('filter_userid') || '';
    document.getElementById('orderid').value = localStorage.getItem('filter_orderid') || '';
    document.getElementById('shiftid').value = localStorage.getItem('filter_shiftid') || '';
    document.getElementById('machineid').value = localStorage.getItem('filter_machineid') || '';
});

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

    const dataTable = document.getElementById('data-table'); 

    if (Array.isArray(data) && data.length > 0) {
        data.forEach(item => {
            const row = document.createElement('tr');
            for (let i = 0; i < numHeaders; i++) {
                const cell = document.createElement('td');
                cell.textContent = item[Object.keys(item)[i]] !== undefined ? item[Object.keys(item)[i]] : 'N/A'; 
                row.appendChild(cell);
            }
            tableBody.appendChild(row); 
        });
        dataTable.style.display = 'table';
    } else {
        document.getElementById('no-data-message').textContent = 'Keine Einträge gefunden.'; 
        dataTable.style.display = 'none';
    }
}


function updatePageTitle(pageName) {
    const titleElement = document.getElementById('page-title');
    titleElement.textContent = pageName;
}

function updateRecordCount(count) {
    document.getElementById('record-count').textContent = `Anzahl: ${count}`;
}