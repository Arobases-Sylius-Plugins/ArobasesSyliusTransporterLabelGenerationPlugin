/************************* *** DATAS *** *************************/

let markers = [];
let map = null;
let markerClusters;
let lat = 48.852969;
let lon = 2.349903;
let points = [];
let submitButton = document.getElementById('selectPickup');
let showUpdateAddressFormButton = document.getElementById('updateOrderAddress');
let updateAddressForm = document.querySelector('#updateOrderAddressForm form');

/************************* *** MAIN *** *************************/

document.addEventListener("DOMContentLoaded", function() {
  submitButton.style.display = "none";
  showUpdateAddressFormButton.addEventListener('click', function (event) {
    clearAddressForm();
    document.getElementById('updateOrderAddressForm').style.display = "block";
  })

  submitButton.addEventListener('click', function (event) {
    event.preventDefault();
    document.getElementById('is_pickup_point').value = true;
    setFullName();
    console.log('submit')
    updateAddressForm.submit();
  })

  showMap();
});

/************************* *** FUNCTIONS *** *************************/

function showMap() {
  let listPointsHtml = '';
  let centerLat = 0;
  let centerLng = 0;

  //clear map
  markers = [];
  let listPointsContainer = document.getElementById('list_points');

  let data = {
    shipping_method_selected_code:  listPointsContainer.dataset.shippingMethodId,
    order_id : listPointsContainer.dataset.orderId,
  };

  let message = "Recherche en cours ...";
  switch(document.getElementsByTagName('html')[0].getAttribute("lang")) {
    case "IT":
      message = "Attualmente in ricerca ...";
      break;
    case "EN":
      message = "Search in progress ...";
      break;
    case "DE":
      message = "Derzeit recherchiert ...";
      break;
  }

  document.getElementById('list_points').innerHTML = "<div class='centerbloc'><div class='inner'>"+message+"<div></div>";

  let url = document.getElementById('list_points').dataset.url
  $.ajax({
    type: 'POST',
    data: data,
    url: url,
    dataType : 'json',
    success: function(data){
      points = [];
      for (let i = 0; i < data.listpoints.length; i++) {
        let point = data.listpoints[i];
        points[i] = point;
      }

      let pointsLength = points.length;
      for(let i = 0; i < pointsLength; i++ ){
        let point = points[i];
        centerLat += parseFloat(point.coordGeolocalisationLatitude) / pointsLength;
        centerLng += parseFloat(point.coordGeolocalisationLongitude) / pointsLength;
      }
      let center = {
        lat: centerLat,
        lng: centerLng
      };
      let zoom = 16;

      if (map == null) {
        initMap(centerLat, centerLng, zoom);
      }else{
        markerClusters.clearLayers();
      }

      if(pointsLength === 0){
        listPointsHtml += 	"<div class='centerbloc'>";
        listPointsHtml += 	"	<div class='inner'>";
        listPointsHtml += 	"		Aucun point relais trouv&eacute;.";
        listPointsHtml += 	"	</div>";
        listPointsHtml += 	"</div>";
        $('#list_points').html(listPointsHtml);
      }
      for (let i = 0; i < pointsLength; i++) {
        let point = points[i];
        let distanceKm = point.distanceEnMetre / 1000;

        listPointsHtml += 	"<div class='point_item' data-id='"+point.identifiant+"' data-index='"+i+"' data-latitude='"+point.coordGeolocalisationLatitude+"' data-longitude='"+point.coordGeolocalisationLongitude+"'>";
        listPointsHtml += 	"	<div><strong>"+point.nom+"</strong></div>";
        listPointsHtml += 	"	<div>"+point.adresse1+"</div>";
        listPointsHtml += 	"	<div>"+point.codePostal+" "+point.localite+"</div>";
        listPointsHtml += 	"	<div>"+distanceKm+" km</div>";
        listPointsHtml += 	"</div>";
        //map
        let content = "";
        let schedules = "";
        schedules += "<table>";
        let openingSchedules = point.openingSchedulesList;
        for (let j = openingSchedules.length-1 ; j >= 0; j--) {
          let openingSchedule = openingSchedules[j];
          let jour = "";
          switch(parseInt(openingSchedule.jour)) {
            case 1:
              jour = "Lundi";
              break;
            case 2:
              jour = "Mardi";
              break;
            case 3:
              jour = "Mercredi";
              break;
            case 4:
              jour = "Jeudi";
              break;
            case 5:
              jour = "Vendredi";
              break;
            case 6:
              jour = "Samedi";
              break;
            case 7:
              jour = "Dimanche";
              break;
          }
          schedules += "<tr><td>" + jour + "</td><td>" + openingSchedule.horairesAsString + "</td></tr>";
        }
        schedules += "</table>";

        content += "<div class='map_window_wrap type_"+point.typerelais+"'>";
        content += "	<table style='width:100%'>";
        content += "		<tr>";
        content += "			<td>";
        content += "				<div class='conten_logo'><img src='"+point.icon_md+"' /></div>";
        content += "			</td>";
        content += "			<td>";
        content += "				<div><strong>"+point.nom+"</strong></div>";
        content += "				<div>"+point.adresse1+"</div>";
        content += "				<div>"+point.codePostal+" "+point.localite+"</div>";
        // content += "				<div><span class='point_map_item ui button primary icon small' data-id='"+point.identifiant+"' data-index='"+i+"'>Choisir ce point</span></div>";
        content += "			</td>";
        content += "		</tr>";
        content += "	</table>";
        content += "	<div>";
        content += "		<div class='map_separateur'></div>";
        content += "		<div>"+schedules+"</div>";
        content += "	</div>";
        content += "</div>";

        let myIcon = L.icon({
          iconUrl: point.icon,
          iconSize: [30, 30],
          iconAnchor: [15, 30],
          popupAnchor: [0, -30],
        });

        let marker = L.marker([point.coordGeolocalisationLatitude, point.coordGeolocalisationLongitude], { icon: myIcon });
        marker.bindPopup(content);
        markerClusters.addLayer(marker);
        markers[i] = marker;
      }
      $('#list_points').html(listPointsHtml);

      let group = new L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.5));
      map.addLayer(markerClusters);

      initEvent();
    },
    error: function(jqXHR, textStatus, errorThrown) {
      //$("#chrono_loading").hide();
    }
  });
}

function hideWindows(map) {
  markers.forEach(function(marker) {
    marker.infowindow.close(map, marker);
  });
}

function initMap(centerLat, centerLng, zoom) {
  map = L.map('map').setView([centerLat, centerLng], zoom);
  markerClusters = L.markerClusterGroup();
  markerClusters.clearLayers();

  L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
    attribution: 'données © OpenStreetMap/ODbL - rendu OSM France',
    minZoom: 1,
    maxZoom: 20
  }).addTo(map);
}

function callBack(index) {
  let point = points[index];

  $.each($('.point_item'), function(index, value ) {
    value.style.backgroundColor = "";
    value.style.padding = "";
    value.style.marginTop = "";
    value.style.marginBottom = "";
    if (value.getAttribute('data-id') == point.identifiant) {
      value.style.backgroundColor = "#bbb";
      value.style.padding = "1rem";
      value.style.marginTop = "1rem";
      value.style.marginBottom = "1rem";
    }
  });

  $.each($('.point_map_item'), function(index, value ) {
    value.style.backgroundColor = "";
    value.style.padding = "";
    value.style.marginTop = "";
    value.style.marginBottom = "";
    if (value.getAttribute('data-id') == point.identifiant) {
      value.style.backgroundColor = "grey";
      value.style.padding = "0.1rem";
      value.style.marginTop = "0.1rem";
      value.style.marginBottom = "0.1rem";
    }
  });

  $('#pickupCompany').empty().append(point.nom);
  $('#pickupStreet').empty().append(point.adresse1);
  $('#pickupCity').empty().append(point.localite);
  $('#pickupPostalcode').empty().append(point.codePostal);
  $('#pickupCountry').empty().append(point.libellePays);

  completeAddressForm(point);
}

function clearPickupInfos() {
  document.getElementById('list_points').innerHTML = "";
  document.getElementById('result').innerHTML = "";
  document.getElementById('pickupCompany').innerHTML = "";
  document.getElementById('pickupStreet').innerHTML = "";
  document.getElementById('pickupCity').innerHTML = "";
  document.getElementById('pickupPhoneNumber').innerHTML = "";
  document.getElementById('pickupCountry').innerHTML = "";
  document.getElementById('pickupPostalcode').innerHTML = "";
}

function initEvent() {
  document.querySelectorAll('.point_item').forEach(element=>{
    element.addEventListener('click',function(event){
      submitButton.style.display = "block";
      let item = null;
      if (event.target.classList.contains('point_item'))
        item = event.target;
      else
        item = event.target.closest('.point_item');
      let index = item.dataset.index;
      map.setView([item.dataset.latitude, item.dataset.longitude], 17);
      markers[index].fire('click');
      callBack(index);
    });
  });
}

function completeAddressForm(point) {
  document.getElementById('sylius_address_company').value = point.nom;
  document.getElementById('sylius_address_street').value = point.adresse1;
  document.getElementById('sylius_address_city').value = point.localite;
  document.getElementById('sylius_address_postcode').value = point.codePostal;
  document.getElementById('sylius_address_pickupPointId').value = point.identifiant;
  document.getElementById('sylius_address_countryCode').querySelectorAll('option').forEach(option=>{
    if (option.value === point.codePays) {
      option.setAttribute('selected', 'selected');
    }
  })
}

function clearAddressForm() {
  updateAddressForm.querySelectorAll('input').forEach(input=>{
    if (input.id !== "sylius_address__token")
      input.value = "";
  });
  updateAddressForm.querySelectorAll('select').forEach(select=>{
    select.value = "";
  });

  setFullName();
}

function setFullName() {
  document.getElementById('sylius_address_firstName').value = document.getElementById('updateOrderAddressForm').dataset.firstname;
  document.getElementById('sylius_address_lastName').value = document.getElementById('updateOrderAddressForm').dataset.lastname;
}