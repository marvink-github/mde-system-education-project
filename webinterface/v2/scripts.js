document.addEventListener("DOMContentLoaded", () => {
    loadMachineData();

    document.getElementById("filter-form").addEventListener("submit", (e) => {
        e.preventDefault();
        const filters = {
            userid: document.getElementById("userid").value,
            timestamp_from: document.getElementById("timestamp_from").value,
            timestamp_to: document.getElementById("timestamp_to").value,
        };
        loadMachineData(filters);
        const modal = bootstrap.Modal.getInstance(document.getElementById('filterModal'));
        modal.hide();
    });
});

function loadMachineData(filters = {}) {
    let endpoint = 'http://127.0.0.1/api/api/getMachinedata';
    
    const queryString = new URLSearchParams(filters).toString();
    if (queryString) {
        endpoint += `?${queryString}`;
    }

    fetch(endpoint, {
        method: 'GET',
        headers: {
            'ApiKey': '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const tbody = document.getElementById('data-table-body');
        tbody.innerHTML = ''; 

        data.forEach(item => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.idMachinedata}</td>
                <td>${item.timestamp}</td>
                <td>${item.userid}</td>
                <td>${item.value}</td>
                <td>${item.order}</td>
                <td>${item.shift_idshift}</td>
            `;
            tbody.appendChild(row);
        });
    })
    .catch(error => console.error("Fehler beim Abrufen der Daten:", error));
}
