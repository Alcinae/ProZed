$.get('map.php').then(function(data) {
    var map = L.map('map').setView([data.user.latitude, data.user.longitude], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    var i, len;
    for (i = 0, len = data.others.length; i < len; ++i) {
        L.marker([data.others[i].latitude, data.others[i].longitude]).addTo(map)
            .bindPopup(data.others[i].fname+"</br>"+data.others[i].email)
            .openPopup();
    }
    

});


