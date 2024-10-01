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
        } else if(entityTypeSelect.value === 'device') {
            deviceFields.classList.remove("d-none");
            machineFields.classList.add("d-none");
        } else {
            deviceFields.classList.remove("d-none");
            machineFields.classList.remove("d-none");
        }
    });

    // Funktion zum Abrufen der Daten
    fetchDataButton.addEventListener("click", function () {
        const userid = document.getElementById("userid-input").value;
        const orderid = document.getElementById("orderid-input").value;
        const shiftid = document.getElementById("shiftid-input").value;
        const machineid = document.getElementById("machineid-input").value;
        const from = document.getElementById("from-date").value;
        const to = document.getElementById("to-date").value;
        const page = document.getElementById("page-input").value;
        const limit = document.getElementById("limit-input").value;

        let params = [];
        if (userid) params.push(`userid=${userid}`);
        if (orderid) params.push(`orderid=${orderid}`);
        if (shiftid) params.push(`shiftid=${shiftid}`);
        if (machineid) params.push(`machineid=${machineid}`);
        if (from) params.push(`from=${from}`);
        if (to) params.push(`to=${to}`);
        if (page) params.push(`page=${page}`);
        if (limit) params.push(`limit=${limit}`);

        const endpoint = document.getElementById("endpoint-select").value;
        const apiKey = "694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c";
        const paramString = params.length ? `?${params.join('&')}&apikey=${apiKey}` : `?apikey=${apiKey}`;        

        fetch(`http://127.0.0.1/api/api/${endpoint}${paramString}`, {
            method: 'GET',
            headers: {
                'ApiKey': apiKey 
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Netzwerkantwort war nicht okay');
                });
            }
            return response.json();
        })
        .then(data => {
            displayData(data); 
            const modalElement = document.getElementById('getModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); 
            showAlert("Daten erfolgreich abgerufen!", "success");
        })
        .catch(error => {
            const modalElement = document.getElementById('getModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); 
            console.error('Fehler beim Abrufen der Daten:', error);            
            showAlert(error.message, "danger");
        });
    });

    backButton.addEventListener("click", function () {
        dataDisplay.style.display = "none";
        document.querySelector(".container").style.display = "block"; 
    });

    openFilterButton.addEventListener("click", function () {
        const getModal = new bootstrap.Modal(document.getElementById('getModal')); 
        getModal.show();
    });

    function displayData(data) {
        const dataBody = document.getElementById("data-body");
        dataBody.innerHTML = ""; 

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

        dataDisplay.style.display = "block";
        document.querySelector(".container").style.display = "none";
    }

    // POST-Anfrage senden, wenn das Formular abgeschickt wird
    const postForm = document.getElementById("postForm");
    postForm.addEventListener("submit", function (event) {
        event.preventDefault(); 
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
                return response.json().then(errorData => {
                    throw new Error(errorData.message || 'Netzwerkantwort war nicht okay');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log("Erfolgreich gesendet:", data);
            const modalElement = document.getElementById('postModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); 
            postForm.reset(); 
            showAlert(data.message, "success");
        })
        .catch(error => {
            const modalElement = document.getElementById('postModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide(); 
            console.error('Fehler beim Senden der Daten:', error);
            showAlert(error.message, "danger");
        });
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
                showAlert("Daten erfolgreich aktualisiert!", "success");
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                console.log(xhr);
                const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Unbekannter Fehler';
                $('#error-message').text(errorMessage).show();
                $('#success-message').hide();
                showAlert(errorMessage, "danger");
            }
        });        
    });
});

document.getElementById('submitDeleteButton').addEventListener('click', function() {
    const machineId = document.getElementById('deleteMachineId').value;
    const apiKey = '694d3da45d8cbcc7fa3fa4d21649a47ff1bf1ad23dd145b0d26fec420f603a2c'; 

    fetch(`http://127.0.0.1/api/api/deleteMachine?machineid=${machineId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'ApiKey': apiKey
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || 'Netzwerkantwort war nicht okay');
            });
        }
        return response.json();
    })
    .then(data => {
        console.log("Erfolgreich gelöscht:", data);
        $('#deleteModal').modal('hide');
        showAlert(data.message, "success");
    })
    .catch(error => {
        console.error('Fehler beim Löschen:', error);
        showAlert(error.message, "danger");
    });
});

function showAlert(message, type) {
    const alertPlaceholder = document.getElementById('alertPlaceholder');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = message;
    alertPlaceholder.appendChild(alert);

    // Automatisches Schließen der Alert-Nachricht nach 4 Sekunden
    setTimeout(() => {
        alert.classList.remove('show');
        // Hier bleibt die fade-Klasse, damit die Animation erfolgt
        alert.classList.add('fade');

        // Entferne das Alert-Element nach der Animation
        alert.addEventListener('transitionend', () => {
            alert.remove();
        });
    }, 4000); // Wartezeit für das automatische Schließen auf 4 Sekunden
}
