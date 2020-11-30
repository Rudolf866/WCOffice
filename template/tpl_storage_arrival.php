<div class="main">
   <div class="category_label">
      Создать движение по складам
      <hr color="#e3e9f1" size="1px" width="850px">
   </div>
   <form class="storage_arrival" action="/iwaterTest/backend.php" method="post">
      <label for="storage">Целевой склад: </label><select id="storage" class="storage" name="storage"></select>
      <label for="arrival_type">Тип движения: </label><select id="storage" class="storage" name="arrival_type" onchange="changeSourceBlock(this.value);">
        <option value="1">Списание</option>
        <option value="2">Приход</option>
        <option value="3">Перемещение</option>
      </select>
      <label for="unit">Товар</label><select class="unit" id="unit" name="unit">

      </select>
      <label for="count">Количество</label><input type="text" id="count" name="count" value="" onkeyup="this.value = this.value.replace (/[^0-9]/, ''); if (this.value < 1) { this.value = ''; }">

      <!-- СЮДА БУДЕТ ВСТАВЛЕН ЭЛЕМЕНТ С ВЫБОРОМ ИСТОЧНИКА ДВИЖЕНИЙ, В ЗАВИСИМОСТИ ОТ ЕГО ТИПА -->
      <div class="temp_source"></div>

      <label for="comment">Комментарий</label><textarea name="comment" id="comment" rows="8" cols="80"></textarea>
      <input type="submit" class="button_storage_arrival" name="storage_arrival" value="Добавить">
   </form>
</div>

<script type="text/javascript">
   $(document).ready(function () {
      $('.storage_arrival').validate({
         rules: {
             count: {
                required: true
             },
             comment: {
                required: true
             }
          },
          messages: {
             count: {
                required: "Заполните поле!"
             },
             comment: {
                required: "Заполните поле!"
             }
          }
      });
   });

   /**
    * Подгрузка складов
   */
   $.ajax({
      type: 'POST',
      url: '/iwaterTest/backend.php',
      data: {
         category_list: ""
      },
      response: "json",
      success: function (res) {
         var category = JSON.parse(res);

         for (var i = 0; i < category.length; i++) {
            $('<option>', { id: i, value: category[i]['id'], text: category[i]['name']}).appendTo('#storage');
            $('<option>', { id: i, value: category[i]['id'], text: category[i]['name']}).appendTo('#source');
         }
      }
   });

   /**
    * Подгрузка товаров
   */
   $.ajax({
      type:'get',
      url:'/iwaterTest/backend.php',
      data:{'company_id':'1'},
      response:'text',
      success:function (data) {
         unit_list = JSON.parse(data);

         for (var i = 0; i < unit_list.length; i++) {
            $('<option>', { id: i, value: unit_list[i]['id'], text: unit_list[i]['name']}).appendTo('#unit');
         }
      }
   });

   var list_input = ['#count', '#comment'];

   function changeSourceBlock(id) {
     var html = '';
     if (id == 1) {
       $('.temp_source').html('');
     } else if (id == 2) {
       $('.temp_source').html('<label for="source">Поставщик: </label><select id="source" class="storage" name="source"></select>');

       /**
        * Подгрузка складов
       */
       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             provider_list: ""
          },
          response: "json",
          success: function (res) {
             var provider = JSON.parse(res);

             for (var i = 0; i < provider.length; i++) {
                $('<option>', { id: i, value: provider[i]['id'], text: provider[i]['name']}).appendTo('#source');
             }
          }
       });
     } else {
       $('.temp_source').html('<label for="source">Источник: </label><select id="source" class="storage" name="source"></select>');

       /**
        * Подгрузка складов
       */
       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             category_list: ""
          },
          response: "json",
          success: function (res) {
             var category = JSON.parse(res);

             for (var i = 0; i < category.length; i++) {
                $('<option>', { id: i, value: category[i]['id'], text: category[i]['name']}).appendTo('#source');
             }
          }
       });
     }
   }
</script>

<style>
.storage_arrival {
   float: left;
   display: grid;
   background-color: #fff;
   border-radius: 5px;
   padding: 15px;
}
.storage_arrival input {
  width: 178px;
}
.temp_source {
  display: grid;
}
.main {
   display: flow-root;
}
.category_label {
   float: left;
   font-size: 21px;
   padding: 10px 0 20px 25px;
}
.button_storage_arrival {
   max-width: 120px;
   margin-left: 530px;
}
label.error {
   position: relative;
}
</style>
