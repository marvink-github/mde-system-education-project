document.addEventListener("DOMContentLoaded", function () { 
    // GET-Anfrage Logik
    const fetchDataButton = document.getElementById("fetchDataButton");
    const dataDisplay = document.getElementById("dataDisplay");
    const backButton = document.getElementById("backButton");
    const entityTypeSelect = document.getElementById("entityType");
    const machineFields = document.getElementById("machineFields");
    const deviceFields = document.getElementById("deviceFields");

    // Event Listener für den Wechsel des Entity-Typs
    entityTypeSelect.addEventListener("change", function () {
        if (entityTypeSelect.value === "machine") {
            machineFields.classList.remove("d-none");
            deviceFields.classList.add("d-none");
        } else {
            deviceFields.classList.remove("d-none");
            machineFields.classList.add("d-none");
        }
    });

    // Funktion zum Abrufen der Daten
    fetchDataButton.addEventListener("click", function () {
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
        const endpoint = document.getElementById("endpoint-select").value;
        const apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";
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

    // Back-Button Logik
    backButton.addEventListener("click", function () {
        dataDisplay.style.display = "none";
        document.querySelector(".container").style.display = "block"; 
    });

    // Modal für Filter öffnen
    openFilterButton.addEventListener("click", function () {
        const getModal = new bootstrap.Modal(document.getElementById('getModal')); 
        getModal.show();
    });

    // Funktion zum Anzeigen der Daten
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

    // POST-Anfrage senden, wenn das Formular abgeschickt wird
    const postForm = document.getElementById("postForm");
    postForm.addEventListener("submit", function (event) {
        event.preventDefault(); // Verhindert das Standard-Formularverhalten
        const apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";
        let postData = {};

        if (entityTypeSelect.value === "machine") {
            postData = {
                name: document.getElementById("machineName").value,
                d_entry_startstop: document.getElementById("dEntryStartstop").value,
                d_entry_counter: document.getElementById("dEntryCounter").value,
                device_idDevice: document.getElementById("deviceId").value
            };
        } else if (entityTypeSelect.value === "device") {
            postData = {
                name: document.getElementById("deviceName").value
                // +++
            };
        }

        const endpoint = entityTypeSelect.value === "machine" ? "postMachine" : "postDevice";

        fetch(`http://127.0.0.1/api/api/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'ApiKey': apiKey
            },
            body: JSON.stringify(postData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht okay');
            }
            return response.json();
        })
        .then(data => {
            console.log("Erfolgreich gesendet:", data);
            const modalElement = document.getElementById('postModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); 
            postForm.reset(); 
        })
        .catch(error => console.error('Fehler beim Senden der Daten:', error));
    });

});

$(document).ready(function() {
    $('#submitPatchButton').click(function() {
        const machineId = $('#machineId').val();
        const updatedUserId = $('#updatedUserId').val(); 
        const updatedOrderId = $('#updatedOrderId').val();  
        const updatedName = $('#updatedName').val();
        const updatedState = $('#updatedState').val();
        const updatedDEntryStartstop = $('#updatedDEntryStartstop').val();
        const updatedDEntryCounter = $('#updatedDEntryCounter').val();
        const updatedDeviceId = $('#updatedDeviceId').val();
        const apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";

        const requestData = {
            machineid: machineId,
            userid: updatedUserId,  
            orderid: updatedOrderId,  
            name: updatedName,
            state: updatedState,
            d_entry_startstop: updatedDEntryStartstop,
            d_entry_counter: updatedDEntryCounter,
            device_idDevice: updatedDeviceId
        };

        $.ajax({
            url: 'http://127.0.0.1/api/api/patchMachine',
            type: 'PATCH',
            contentType: 'application/json',
            dataType: 'json', 
            headers: {
                'ApiKey': apiKey
            }, 
            data: JSON.stringify(requestData),
            success: function(response) {
                $('#success-message').text(response.message).show();
                $('#patchModal').modal('hide');
                $('#error-message').hide();
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                console.log(xhr);
                $('#error-message').text(xhr.responseJSON ? xhr.responseJSON.message : 'Unknown error').show();
                $('#success-message').hide();
            }
        });        
    });
});


