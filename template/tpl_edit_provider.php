<?php
$id = trim(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
$provider = get_provider_by_id($id);
?>
<div class="main">
<form id="inputStorage" style="width:850px; height: 450px; margin:0 0 0 25px;display: inline-block;">
  <div class="name_title">
    <div class="name_position">Редактировать поставщика</div>
  </div>
  <fieldset>
    <input name="id_db" id = "id"type="hidden" value="<?php echo $provider[0]['id'] ?>">
    <table style="width: 100%; display: inline-block; margin: 0 5px;">
      <tbody style="width: 100%; display: inline-block;">
        <tr>
          <td style="width: 350px"> Имя:</td>
        </tr>
        <tr style="width: 100%; display: inline-block;">
          <td style="width: 350px; display: inline-block;"><input type="text" id="name" value="<?php echo $provider[0]['name'] ?>" style="width: 80%;"/></td>
        </tr>
        <tr>
          <td style="width: 350px"> Контакты:</td>
        </tr>
        <tr style="width: 100%; display: inline-block;">
          <td style="width: 350px; display: inline-block;"><input type="text" id="contact" value="<?php echo $provider[0]['contact'] ?>" style="width: 80%;" /></td>
        </tr>
     </tbody>
    </table>

  </fieldset>
  <input class="search_button" type="button" id="savedata" value="Редактировать" onclick="editNewProvider();" style="float: right;     margin-bottom: 30px;" />
  <input class="reset_button" type="button" id="savedata" value="Отменить" onclick="window.location.reload();" style="float: right;     margin-bottom: 30px;" />
</form>
</div>

<script type="text/javascript">
   function editNewProvider() {
      var name = $("#name").val();
      var contact = $("#contact").val();
      var id = $("#id").val();
      if (name != '' && contact != '') {
         $.ajax({
            type: "POST",
            url: "/iwaterTest/backend.php?units",
            data: {
              edit_provider: "edit",
              id: id,
              name: name,
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
