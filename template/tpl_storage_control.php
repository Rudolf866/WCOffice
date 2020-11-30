<div class="main">
   <div class="main_form">
     <div class="category_label">
        Список складов
        <hr color="#e3e9f1" size="1px" width="100%">
     </div>
     <div class="storage_block">
         <table id="storage_table"></table>
     </div>
   </div>
</div>

<script type="text/javascript">
    $("#storage_table").jqGrid({
      url: "/iwaterTest/backend.php?storage_info",
      datatype: "xml",
      mtype: "POST",
      xmlReader: {
        root:"rows",
        total:"total",
        records:"rows>records",
        repeatitems:true
      },
      colNames: ["Номер склада", "Наименование склада", "Приоритет склада", "Адрес склада", "Имя кладовщика", "Контакты кладовщика", "Редактировать", "Удалить"],
      colModel: [
        {name: "id", index: "id", width: 90, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
        {name: "name", index: "name", width: 145, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
        {name: "priority", index: "priority", width: 140, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
        {name: "address", index: "address", width: 190, align: "center", sorttype: 'string', editable: false, editoptions: { cols: 40 }, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
        {name: "storeman_name", index: "storeman_name", width: 140, align: "center", datatype: 'html', editable: false},
        {name: "storeman_phone", index: "storeman_phone", width: 160, align: "center", sorttype: 'string', amount:"200.00", editable: false, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
        {name: "edit", index: "edit", width: 100, align: "center", sorttype: 'string', amount:"200.00", editable: false},
        {name: "delete", index: "delete", width: 70, align: "center", formatter: setDeleteButton}
      ],
      pager: "#pager",
      rowNum: 30,
      rowList: [30, 50, 100],
      sortname: 'id',
      viewrecords: true,
      sortorder: "desc",
      editurl: "/iwaterTest/backend.php?storage=1",
      gridview: true,
      autoencode: false,
      loadonce: false,
      sortable: true,
      multiselect: false
    });

    $("#storage_table").jqGrid('setGridHeight', 'auto');


   /**
    * Добавить новый склад
   */
   function addNewStorage() {
      $.ajax({
         type: 'POST',
         url: '/iwaterTest/backend.php',
         data: {
            add_storage: ''
         },
         response: 'json',
         success: function() {
            document.location.href = '/iwaterTest/admin/storage_control/';
         }
      });
   }

   /**
    * Удалить склад
   */
   function deleteStorage(event) {
      var current = event.path[1].className;
      current = $('.' + current);

      $.ajax({
         type: 'POST',
         url: '/iwaterTest/backend.php',
         data: {
            delete_storage: current.children('#id').val()
         },
         response: 'text',
         success: function(res) {
            document.location.href = '/iwaterTest/admin/storage_control/';
         }
      });
   }

   /**
    * Отображение местоположения и формирование координат
   */
   function getPosition(event) {
      var current = event.path[1].className;
      var address = $('.' + current).children('#address').val();

      $.ajax({
         type: 'GET',
         url: 'https://geocode-maps.yandex.ru/1.x/',
         data: {
            geocode: address
         },
         response: 'xml',
         success: function (res) {
            var coord = res.getElementsByTagName('GeoObjectCollection')[0].getElementsByTagName('featureMember')[0].getElementsByTagName('GeoObject')[0].getElementsByTagName('Point')[0].getElementsByTagName('pos')[0].childNodes[0].nodeValue;

            coord = coord.split(' ');

            $('.map').css('display', 'block');
            $('.overlay').css('display', 'block');

            myMap = new ymaps.Map("map", {
                center: [coord[1], coord[0]],
                zoom: 10
            });

            var myGeoObject = new ymaps.GeoObject({
                geometry: {
                    type: "Point", // тип геометрии - точка
                    coordinates: [coord[1], coord[0]] // координаты точки
                }
            });

            myMap.geoObjects.add(myGeoObject);
         }
      });

   }

   // Кнопка удаления
   function setDeleteButton(options) {
      return '<a><img src="/iwaterTest/css/image/delete.png" style="cursor: pointer;" onclick="if (confirm(\' Подтвердить удаление?\')) {deleteStorage(' + options + ');} else { return false; }"></a>';
   }

   // Метод удаления поставщика с оповещением о успешном завершении
   function deleteStorage(val) {
      $.ajax({
         type: "POST",
         url: "/iwaterTest/backend.php",
         data: {
            delete_storage: val
         },
         success: function () {
            alert('Запись успешно удалена');
            window.location.reload();
         }
      });
   }
</script>

<!-- ВСЁ ЭТО СТОИТ ПЕРЕНЕСТИ ПОСЛЕ ВЫХОДА НА ПРОД -->
<style>
   #storage {
      background-color: #fff;
      border-radius: 10px;
      display: grid;
      padding: 8px;
      width: 300px;
      margin-top: 15px;
      margin-left: 15px;
      float: left;
   }
   #storage a {
      color: #000;
      text-decoration: none;
   }
   #add_storage {
      border-radius: 12px;
      border: 1px solid #015aaa;
      display: grid;
      padding: 8px;
      width: 60px;
      text-align: center;
      float: left;
      margin-top: 115px;
      margin-left: 15px;
   }
   #add_storage:hover {
      background: #015aaa;
      color: #f4f4f4;
   }
   #coords {
      background: #015aaa;
      border: 0px;
      border-radius: 9px;
      color: #fff;
      min-height: 28px;
   }
   #coords:hover {
      background: #74ccea;
      color: #fff;
   }
   #save {
      background: #015aaa;
      border: 0px;
      border-radius: 9px;
      color: #fff;
      min-height: 28px;
   }
   #save:hover {
      background: #74ccea;
      color: #fff;
   }
   #delete {
      background: #74ccea;
      border: 0px;
      border-radius: 21px;
      color: #fff;
      width: 18px;
      position: fixed;
      float: right;
      margin: 3px 0px 0px 294px;
      height: 18px;
      padding: 0px 0px 2px 0px;
      cursor: pointer;
   }

   .map {
      background-color: #807e7e;
      width: 500px;
      height: 500px;
      position: absolute;
      left: 40%;
      z-index: 1;
   }
   .overlay {
      background-color: #adadad;
      position: absolute;
      width: 100%;
      height: 100%;
      opacity: .6;
      left: 0px;
      top: 0px;
   }
   .close_map {
      position: relative;
      float: right;
      margin-right: 3px;
      background: #807e7e;
      border: 0px;
      border-radius: 9px;
      color: #fff;
      min-height: 28px;
   }
   .main_form {
     display: grid;
   }
   .category_label {
      float: left;
      font-size: 21px;
      padding: 20px 0 20px 25px;
   }
   .storage_block {
     margin: 0 0 0 15px;
   }
</style>
