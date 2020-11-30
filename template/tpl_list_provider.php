<?php
   $access_level = check_perms('list_provider');
?>

 <div class="main">
    <div class="name_title">
                <div class="name_position">Список поставщиков</div>
            </div>
            <br>
   <div class="list_conteiner" style="overflow-x: hidden;">
      <table id="list_provider"></table>
   </div>

      <script type="text/javascript">
        var grid = $("#list_provider"), MAX_PAGERS = 2;
         $("#list_provider").jqGrid({
             url: "/iwaterTest/backend.php?list_provider",
             datatype: "xml",
             mtype: "POST",
             xmlReader: {
                root:"rows",
                total:"total",
                records:"rows>records",
                repeatitems:true
             },
             colNames: ["Имя", "Контакты", "Редактировать", "Удалить"],
             colModel: [
                {name: "name", index: "name", width: 90, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "contact", index: "contact", width: 130, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "edit", index: "edit", width: 100, align: "center", <?php if ($access_level < 2) echo 'hidden: true,'; ?> sorttype: 'string', amount:"200.00", editable: false},
                {name: "delete", index: "delete", width: 70, align: "center", <?php if ($access_level < 3) echo 'hidden: true,'; ?> formatter: setDeleteButton}
             ],
             rowNum: 30,
             rowList: [30, 50, 100],
             sortname: 'name',
             viewrecords: true,
             sortorder: "desc",
             height: "auto",
             editurl: "/iwaterTest/backend.php",
             gridview: true,
             autoencode: false,
             caption: "Контрагенты",
             loadonce: false,
             sortable: true,
         });
         $("#list_provider").jqGrid('setGridWidth',1015);
         $("#list_provider").jqGrid('navGrid', "#pager", {edit: false, add: false, del: false}, {}, {}, {}, {
            multipleSearch: true,// Поиск по нескольким полям
            multipleGroup: true, // Сложный поиск с подгруппами условий
            showQuery: true
         });

         $("#list_provider").jqGrid('setGridHeight', 'auto');

         //var localPositionMargin = 0; // Из за анимации позиция не сразу проставляется конечной, из-за этого можно улететь за границу таблицы, этот фикс считает без задержки анимаций

         // Метод для прокрутки таблицы стрелочками
         function horizationScrollControl(side) {
            var min = -384;
            var max = 0;
            var tableBlock = $('#gbox_list');

            if (side == 'left' && localPositionMargin < max) {
              localPositionMargin += 128;
              tableBlock.animate({
                   'margin-left': "+=128px" // уменьшение ширины границы элемента на два пикселя от текущего значения
              }, '1', "linear");
            } else if (side == 'right' && localPositionMargin > min) {
              localPositionMargin -= 128;
              tableBlock.animate({
                   'margin-left': "-=128px" // уменьшение ширины границы элемента на два пикселя от текущего значения
              }, '1', "linear");
            }
         }

         // Кнопка удаления
         function setDeleteButton(options) {
            return '<a><img src="/iwaterTest/css/image/delete.png" style="cursor: pointer;" onclick="if (confirm(\' Подтвердить удаление?\')) {deleteProvider(' + options + ');} else { return false; }"></a>';
         }

         // Метод удаления поставщика с оповещением о успешном завершении
         function deleteProvider(val) {
            console.log(val);
            $.ajax({
               type: "POST",
               url: "/iwaterTest/backend.php",
               data: {
                  delete_provider: val
               },
               success: function () {
                  alert('Запись успешно удалена');
                  window.location.reload();
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
