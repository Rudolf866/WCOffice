<?php
   $access_level = check_perms('list_companies');
?>

  <div class="main">
    <div class="name_title">
                <div class="name_position">Список компаний</div>
                <div class="add_position"><a href="/iwaterTest/admin/add_company/"><span class="img_add"></span>Добавить компанию</a></div>
            </div>
            <br>
   <div class="list_conteiner">
      <table id="list"></table>
   </div>
    <div id="pagination">
                <?php
                $lists_in_page = 10;
                $iCurr = (empty($_GET['page']) ? 1 : intval($_GET['page']));
                ?>
            </div>
    </div>

      <script type="text/javascript">
        var grid = $("#list"), MAX_PAGERS = 2;
         $("#list").jqGrid({
             url: "/iwaterTest/backend.php?company",
             datatype: "xml",
             mtype: "POST",
             xmlReader: {
                root:"rows",
                total:"total",
                records:"rows>records",
                repeatitems:true
             },
             colNames: ["Редактирование", "№ компании", "Название", "Регион", "Адрес", "Контакты", "Время работы", "Регионы доставки"],
             colModel: [
                {name: "edit", index: "edit", width: 120, align: "center", sorttype: 'string', search: true, <?php if ($access_level < 2) { echo 'hidden: true,'; } ?> editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "id", index: "id", width: 90, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "name", index: "name", width: 130, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "city", index: "city", width: 130, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "address", index: "address", width: 210, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "contact", index: "contact", width: 210, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "schedule", index: "schedule", width: 250, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
                {name: "regions", index: "regions", width: 370, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}}
             ],
             rowNum: 30,
             rowList: [30, 50, 100],
             sortname: 'id',
             viewrecords: true,
             sortorder: "desc",
             onPaging: function () {},
             onSortCol: function () {},
             loadComplete: function () {},
             onSelectRow: function (id) {   },
             editurl: "/iwaterTest/backend.php",
             gridview: true,
             autoencode: false,
             caption: "Компании",
             loadonce: false,
             sortable: true,
         });

         $("#list").jqGrid('navGrid', "#pager", {edit: false, add: false, del: false}, {}, {}, {}, {
            multipleSearch: true,// Поиск по нескольким полям
            multipleGroup: true, // Сложный поиск с подгруппами условий
            showQuery: true
         });

         $("#list").jqGrid('setGridHeight', 'auto');
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
        /* height: 400px !important; */
        /* width: 1020px !important; */
    }

    .ui-jqgrid .ui-jqgrid-view,
    .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
        /* width: 1020px !important; */
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
.list_conteiner {
   overflow: auto;
   max-height: 300px;
}
</style>
