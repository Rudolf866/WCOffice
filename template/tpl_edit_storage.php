<div class="main">
   <form id="iputStorage" style="width:850px; height: 450px; margin:0 0 0 25px;display: inline-block;">
     <div class="name_title">
       <div class="name_position">Редактировать склад</div>
     </div>
     <fieldset>
       <table style="width: 100%; display: inline-block; margin: 0 5px;">
         <tbody style="width: 100%; display: inline-block;">
           <tr>
             <td style="width: 350px"> Наименование:</td>
             <td style="width: 350px"> Имя кладовщика:</td>
           </tr>
           <tr style="width: 100%; display: inline-block;">
             <td style="width: 350px; display: inline-block;"><input type="text" id="storage_name" style="width: 80%;"/> <span class="leng_alert" style="color:red; display: none;">Максимальная длина 86 символов</span></td>
             <td style="width: 350px; display: inline-block;"><input type="text" id="storageman_name" style="width: 80%;" /></td>
           </tr>
           <tr>
             <td style="width: 350px"> Приоритет склада:</td>
             <td style="width: 350px"> Контакты кладовщика:</td>
           </tr>
           <tr style="width: 100%; display: inline-block;">
             <td style="width: 350px; display: inline-block;"><select id="priority" name="priority" style="min-width: 80%;">
                <option value="0">Главный</option>
                <option value="1">Второстепенный</option>
                <option value="2">Региональный</option>
             </select>
             </td>
             <td style="width: 350px; display: inline-block;"><input type="text" id="storageman_contact" style="width: 80%;" /></td>
           </tr>
         <tr>
           <td style="width: 350px;"> Адрес склада:</td>
         </tr>
         <tr style="width: 100%; display: inline-block;">
           <td style="width: 350px; display: inline-block;"><input type="text" id="address" style="width: 80%;" /></td>
         </tr>
        </tbody>
       </table>

     </fieldset>
     <input class="search_button" type="button" id="savedata" value="Сохранить" onclick="saveStorage();" style="float: right;     margin-bottom: 30px;" />
     <input class="reset_button" type="button" id="savedata" value="Отменить" onclick="window.location.reload();" style="float: right;     margin-bottom: 30px;" />
   </form>
</div>

<script type="text/javascript">
   $(document).ready(function () {
      selectStorageInfo();
   });

   function saveStorage() {
      var name = $("#storage_name").val();
      var priority = $("#priority").val();
      var address = $("#address").val();
      var storeman = $("#storageman_name").val();
      var contact = $("#storageman_contact").val();

      if (name != '' && address != '') {
         $.ajax({
            type: "POST",
            url: "/iwaterTest/backend.php",
            data: {
              save_storage: "edit",
              id: <?php echo $_GET['id']; ?>,
              name: name,
              priority: priority,
              address: address,
              storeman: storeman,
              contact: contact
            },
            success: function () {
               window.location.reload();
            }
          });
        } else {
          alert('Не введены основные данные!');
        }
   }

   function selectStorageInfo() {
      $.ajax({
         type: 'POST',
         url: '/iwaterTest/backend.php',
         data: {
            storage_info: <?php echo $_GET['id']; ?>
         },
         success: function(res) {
            var resource = JSON.parse(res);
            $("#storage_name").val(resource['name']);
            $("#priority").val(resource['priority']);
            $("#address").val(resource['address']);
            $("#storageman_name").val(resource['storeman_name']);
            $("#storageman_contact").val(resource['storeman_phone']);
         }
      });
   }
</script>

<style media="screen">
  fieldset {
      border: 0;
      background-color: #fff;
      border-radius: 8px;
      margin-top: 30px;
  }

  tbody {
      width: 100% !important;
  }

  fieldset>input {
      width: 100% !important;
  }
</style>
