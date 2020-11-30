<?php
?>

<div class="search_form">
<h3>Поиск водителя</h3>
<select id="select_driver" onchange="get_coord();">
    <option selected>Выбрать водителя</option>
    <?php echo select_driver(); ?>
</select>
<div class="driver_anons"></div>
</div>
<div id="my_socket"></div>
<div id="map" style="margin-left: 200px;"></div>
<img src="http://iwatercrm.ru/iwaterTest/css/image/loading-gallery.gif" class="loading" alt="" style="height: 141px; top: 0; left: 0; right: 0; bottom: 0; position: absolute; margin: auto; display: none;"/>
<script>

    //create a new WebSocket object.
    var url = "ws://95.213.183.181:10030";
    try {
      socketClient = new WebSocket(url);
   } catch (err) {
      alert(err);
   }
    socketClient.onopen = function(ev) { // connection is open
        console.log('opened');
    }

    socketClient.onmessage = function(ev) {
        $('.loading').hide();
        if (ev.data == 'connected') {
          console.log('Подключение прошло успешно');
          socketClient.send(Math.round(1000 + Math.random() * (9999 - 1000)));
        } else if (ev.data == 'offline') {

          $('#map').empty();
          $('.driver_anons').text('Водитель: ' + $("#select_driver option:selected").text() + ' не в сети.');
       } else if (ev.data[0] > 0) {
          $('.driver_anons').text('Получение координат...');
          drawMap(ev.data);
       } else {
          console.log(ev.data + "\n" + "Ошибка подключения");
        }
    }

    socketClient.onerror = function(error) {
      alert('WebSocket error: ' + error.data + '. Call your application developer.');
   }

    function get_coord() {
        socketClient.send('id' + $('#select_driver').val());
        $('.loading').show();
    }

    function drawMap(point) {
         $('#map').empty();

         var coords = point.split(',');
         var myMap = new ymaps.Map('map', {
         center: [coords[0], coords[1]],
         zoom: 17,
         behaviors: ['default', 'scrollZoom'],
         controls: ['zoomControl']
         }, {
         searchControlProvider: 'yandex#search'
         }),

         // Создание геообъекта с типом точка (метка).
         myGeoObject = new ymaps.GeoObject({
             geometry: {
                 type: "Point", // тип геометрии - точка
                 coordinates: [coords[0], coords[1]] // координаты точки
             }
         });

         // Размещение геообъекта на карте.
         myMap.geoObjects.add(myGeoObject);
         $('.driver_anons').text('');
    }
</script>
