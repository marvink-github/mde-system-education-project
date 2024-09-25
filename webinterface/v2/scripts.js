document.addEventListener("DOMContentLoaded", function () {
    const fetchDataButton = document.getElementById("fetchDataButton");
    const dataDisplay = document.getElementById("dataDisplay");
    const backButton = document.getElementById("backButton");

    fetchDataButton.addEventListener("click", function () {
        const endpoint = document.getElementById("endpoint-select").value;
        const apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";

        // Werte aus den Filterfeldern holen
        const userid = document.getElementById("userid-input").value;
        const orderid = document.getElementById("orderid-input").value;
        const shiftid = document.getElementById("shiftid-input").value;
        const machineid = document.getElementById("machineid-input").value;
        const from = document.getElementById("from-date").value;
        const to = document.getElementById("to-date").value;
        const page = document.getElementById("page-input").value;
        const limit = document.getElementById("limit-input").value;

        // Erstelle die URL-Parameter
        let params = [];
        if (userid) params.push(`userid=${userid}`);
        if (orderid) params.push(`orderid=${orderid}`);
        if (shiftid) params.push(`shiftid=${shiftid}`);
        if (machineid) params.push(`machineid=${machineid}`);
        if (from) params.push(`from=${from}`);
        if (to) params.push(`to=${to}`);
        if (page) params.push(`page=${page}`);
        if (limit) params.push(`limit=${limit}`);

        // Verbinde die Parameter zu einem String
        const paramString = params.length ? `?${params.join('&')}&apikey=${apiKey}` : `?apikey=${apiKey}`;

        // API-Anfrage senden
        fetch(`http://127.0.0.1/api/api/${endpoint}${paramString}`, {
            method: 'GET',
            headers: {
                'ApiKey': apiKey 
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht okay');
            }
            return response.json();
        })
        .then(data => {
            displayData(data); // Daten anzeigen
            const modalElement = document.getElementById('getModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); // Schließt das Modal
        })
        .catch(error => console.error('Fehler beim Abrufen der Daten:', error));
    });

    backButton.addEventListener("click", function () {
        dataDisplay.style.display = "none"; // Verstecke die Datenanzeige
        document.querySelector(".container").style.display = "block"; // Zeige die Buttons wieder an
    });

    openFilterButton.addEventListener("click", function () {
        const getModal = new bootstrap.Modal(document.getElementById('getModal')); // Öffne das Popup
        getModal.show();
    });

    function displayData(data) {
        const dataBody = document.getElementById("data-body");
        dataBody.innerHTML = ""; // Leere den vorherigen Inhalt

        // Durchlaufe die Daten und füge sie der Tabelle hinzu
        data.forEach(item => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${item.idMachinedata}</td>
                <td>${item.timestamp}</td>
                <td>${item.userid}</td>
                <td>${item.value}</td>
                <td>${item.order}</td>
                <td>${item.shift_idshift}</td>
            `;
            dataBody.appendChild(row);
        });

        // Datenanzeige aktivieren und Buttons ausblenden
        dataDisplay.style.display = "block";
        document.querySelector(".container").style.display = "none";
    }
});
