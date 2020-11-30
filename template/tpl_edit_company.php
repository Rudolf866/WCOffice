<div class="main">
   <div class="name_title">
                <div class="name_position">Редактировать компанию</div>
            </div>
            <br>
    <div class="main_form" id="edit_form" >
        <form method="post" action="/iwaterTest/backend.php">
          <table class="add_company_tabl">
            <tr>
           <td> <label  class="add_company_name" id="company" for="id">Номер компании</label></td>
           <td style="padding:0px 0px 0px 30px;"> <label class="add_company_rlabel" for="contact">Контактные данные</label></td>
       </tr>
       <tr>
           <td><input style="margin: 0px; width: 115px;" id="company_id" name="id" type="text" placeholder="4 цифры" disabled></td>
           <td style="padding:0px 0px 0px 30px;"><input style="margin: 0px; width: 320px;" id="contact" name="contact" type="text" placeholder="Номер или номера телефона через ,"></td>
        </tr>
        <tr>
            <td><label for="name">Наименование компании</label></td>
             <td style="padding:0px 0px 0px 30px;"> <label for="schedule">Время работы</label></td>
         </tr>
         <tr>
            <td><input style="margin: 0px; width: 260px;" id="company_name" name="name" type="text" placeholder="Наименование"></td>
           <td style="padding:0px 0px 0px 30px;"><input style="margin: 0px; width: 320px;" id="schedule" name="schedule" type="text" placeholder="В форме как будет отображено в приложении"></td>
        </tr>
        <tr>
           <td> <label for="city">Регион</label></td>
           <td style="padding:0px 0px 0px 30px;"> <label for="regions">Регионы доставки</label></td>
       </tr>
       <tr>
           <td><input style="margin: 0px; width: 260px;" id="region" name="city" type="text" placeholder="Город или район"></td>
           <td style="padding:0px 0px 0px 30px;"><input style="margin: 0px; width: 320px;" id="regions" name="regions" type="text" placeholder="В форме как будет отображено в приложении"></td>
        </tr>
          </table>
      </br>
          <input name="edit_company" type="hidden">
          <input class="classic" name="submit" type="submit" value="Сохранить" style="float:right;  ">
            <input class="classic" name="submit" type="submit" style="float:right; background-color: #fff; color: #000" value="Отменить" onclick="window.location.reload();">
                    </form>
            </div> <br>
    </div>
                <div id="pagination">
                <?php
                $lists_in_page = 10;
                $iCurr = (empty($_GET['page']) ? 1 : intval($_GET['page']));
                ?>
            </div>
    <div>

      <script type="text/javascript">
        var grid = $("#list"), MAX_PAGERS = 2;
        selectCompanyInfo();

         // Выгрузка данных о компании
         function selectCompanyInfo() {
            $.ajax({
               type: 'POST',
               url: '/iwaterTest/backend.php',
               data: {
                  company_info: "<?php echo $_GET['id']; ?>"
               },
               success: function(res) {
                  var data = JSON.parse(res);
                  console.log(data);

                  $('#company_id').val(data['id']);
                  $('#contact').val(data['contact']);
                  $('#company_name').val(data['name']);
                  $('#schedule').val(data['schedule']);
                  $('#regions').val(data['regions']);
                  $('#region').val(data['region']);
               }
            });
         }
      </script>
<style>

  .ui-jqgrid .ui-jqgrid-pager,
    .ui-jqgrid .ui-jqgrid-caption {
        display: none !important;
    }

    .btn_link {
        border: none;
        background-color: #fff0;
        cursor: pointer;
    }

    .btn_link:focus {
        color: #015aaa;
    }

    .btn_link:hover {
        color: #015aaa;
    }

    .reset_button:active{
        padding: 2px 30px;
    }

    .s-ico {
        display: none !important;
    }

    .ui-jqgrid .ui-jqgrid-bdiv {
        /* height: 400px !important;
        width: 1020px !important; */
    }

    .ui-jqgrid .ui-jqgrid-view,
    .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
        width: 1020px !important;
    }

    .ui-state-default, .ui-widget-content .ui-state-default {
        /* width: auto !important; */
    }



    .name_title{
        display: flow-root;
    }
    .main_form{
    border-radius: 5px;
    width: 670px;
    height: fit-content;
    text-align: center;
    background-color: white;
    }
    .add_company_tabl{
     width: 670px;
     text-align: left;
    height: 180px;
    padding: 10px 45px 30px 25px;
    border-radius: 10px;
    font-size: 12px;
    float: left;
    background-color: #fff;
    color: #7a7a7a;
    }
    input {
      width: 185px;
      height: 22px;
      border-radius: 5px;
      border-color: #e3e9f1;
      border-style: solid;
   }
   input[type=submit] {
      width: 100px;
      height: 30px;
      background-color: #0157a4;
      color: #fff;
      border-radius: 8px;
  }
  .ui-jqgrid .ui-jqgrid-btable td{
    overflow-wrap: break-word;
  }
  .ui-jqgrid .ui-pg-table .ui-pg-input, .ui-jqgrid .ui-pg-table .ui-pg-selbox {
    height: auto;
    width: auto;
    line-height: inherit;
    padding: 1px;
    font-weight: normal;
    font-size: 11px;
    margin: 1px;
}
.table_scroll_button {
   width: 30px;
   height: 30px;
   background-color: #015aaa;
   color: #fff;
   border-radius: 15px;
   padding: 1px 0px 0px 2px;
   font-size: 18px;
}
.horizontalScrollButton {
   margin: 0 0 0 959px;
}
</style>
