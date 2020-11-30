<?php
$today_time = time();
$week_time = time() - 604800;
?>
<div class="main">
  <div class="name_title">
    <div class="name_position">Информация о складах</div>
    <div class="back_button" style="display: <?php if (isset($_GET['id'])) echo 'block'; else echo 'none'; ?> ;"><a href="/iwaterTest/admin/storage_info"> <span class="img_list"></span> К списку складов</a></div>
  </div>

  <div class="storages_tab">

  </div>
	 <div id="overlay"></div><!-- Пoдлoжкa -->
   <div class="storage" id="storage">
      <table id="list_storage"></table>
   	<div id="pager_list_storage"></div>

      <!-- ВОТ В ЧЁМ ИДЕЯ, КОГДА МЫ ВЫБРАЛИ КАКОЙ-ТО СКЛАД, ТАБЛИЦА СКРЫВАЕТСЯ И ОТКРЫВАЕТСЯ ВТОРАЯ, ГДЕ МЫ ВИДИМ УЖЕ ИСТОРИЮ -->

   	<table id="list_info_storage"></table>
   	<div id="pager_info_storage"></div>
   	<script type="text/javascript">
       $("#list_info_storage").jqGrid({
          mtype: "POST",
          url: '/iwaterTest/backend.php?list_info_storage=<?php if (isset($_GET['id'])) { echo $_GET['id']; }?>',
          datatype: "xml",
          colNames:['count_id','product_id','Товар', 'Количество','id_склада', 'Склад', 'Дата последней трансакции', 'Комментарий'],
          colModel:[
             {name:'count_id', index:'count_id',  width:100,editable:true, editrules:{ required:true, edithidden:false }, hidden:true },
             {name:'product_id', index:'product_id',  width:100,editable:true, editrules:{ required:true, edithidden:false }, hidden:true },
             {name:'product',  key : true, index:'a.name', autowidth: true, shrinkToFit:false /*width:5*/, align:"center", sortable: true, search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
             {name:'count', index:'c.count', align:"center", width:140, sortable: true,  search: true, editable: true, number: true, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
             {name:'storage_id', index:'storage_id',  width:100,editable:true, editrules:{ required:true, edithidden:false }, hidden:true },
             {name:'storage_name', index:'c.storage', align:"center",   width:140, search: true,  sortable: true, editable: true, edittype: 'select', editoptions:{value: storage },   searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
             {name:'last_update', index:'c.last_update', align:"center", width:165, search: true, sortable: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
             {name:'comment', index:'comment',  width:100,editable:true, editrules:{ required:true, edithidden:true }, hidden:true }
          ],
          editurl: "/iwaterTest/backend.php?info_storage",
         // cellEdit: true,
          viewrecords: true,
          caption: 'Информация о складах',
          sortable: true,
          shrinkToFit: false,
          pager: "#pager_info_storage",
          rowNum: 30,
          ondblClickRow: function(rowid, iRow, iCol, e) {
             var product = $("#list_info_storage").getCell(rowid, 'product_id');
             var storage = $("#list_info_storage").getCell(rowid, 'storage_id');
             window.location.href = "/iwaterTest/admin/transaction_info?storage=" + storage + "&unit=" + product + "&from=" + <?php echo $week_time; ?> + "&to=" + <?php echo $today_time; ?>;
          },
          rowList: [30, 50, 100],
          sortname: 'a.name',
          viewrecords: true,
          sortorder: "desc",
          height: 385
       });

       $("#list_info_storage").jqGrid('navGrid', "#pager_info_storage", {edit: true, add: false, del: false, nav: true, search: true, refresh: false}, {}, {}, {}, {
          multipleSearch: true,// Поиск по нескольким полям
          multipleGroup: false, // Сложный поиск с подгруппами условий
          showQuery: true
       });

       $("#list_storage").jqGrid({
          mtype: "POST",
          url: '/iwaterTest/backend.php?category_list',
          datatype: "xml",
          colNames:['Список складов'],
          colModel:[
             {name:'name', index:'name',  width:310, editable:false, editrules:{ required:true, edithidden:false } }
          ],
          viewrecords: true,
          caption: 'Список складов',
          sortable: true,
          shrinkToFit: false,
          pager: "#pager_list_storage",
          rowNum: 30,
          ondblClickRow: function(rowid, iRow, iCol, e) {
             window.location.href = "/iwaterTest/admin/storage_info?id=" + rowid;
          },
          rowList: [100],
          sortname: 'a.name',
          viewrecords: true,
          sortorder: "desc"
       });

       $("#list_storage").jqGrid('setGridHeight', 'auto');
      </script>
   </div>
</div>

<style>
#storage {
   width: 100%;
   height: 100%;
   display: flex;
   margin: 0px 0 0 25px;
   flex-flow: row wrap;
}
 #overlay {
   	z-index:999; /* пoдлoжкa дoлжнa быть выше слoев элементoв сaйтa, нo ниже слoя мoдaльнoгo oкнa */
   	position:fixed; /* всегдa перекрывaет весь сaйт */
   	background-color:#000; /* чернaя */
   	opacity:0.8; /* нo немнoгo прoзрaчнa */
   	-moz-opacity:0.8; /* фикс прозрачности для старых браузеров */
   	filter:alpha(opacity=80);
   	width:100%;
   	height:100%; /* рaзмерoм вo весь экрaн */
   	top:0; /* сверху и слевa 0, oбязaтельные свoйствa! */
   	left:0;
   	cursor:pointer;
   	display:none; /* в oбычнoм сoстoянии её нет) */
   }
/*Таблица jqgrid*/
    .ui-jqgrid .ui-jqgrid-pager,
    .ui-jqgrid .ui-jqgrid-caption {
        display: none !important;
    }

    .ui-th-column {
       background: #000 !important;
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
          .ui-jqgrid-htable ui-common-table{
        width: 100%;
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
        width: auto !important;
    }

    .storages_tab {
      max-width: 1000px;
      overflow: visible;
    }
    .storages_tab ul {
      width: auto;
      margin-bottom: 0px;
      padding-left: 25px;
      display: inline-flex;
    }
    .storages_tab li {
      min-width: 100px;
      margin: 1px;
      list-style-type: none;
    }
    #list_storage {
       width: 333px !important;
    }
    .storages_tab input {
      width: 100%;
      height: 100%;
      border: 0px;
      border-radius: 10px 10px 0 0;
      background-color: #74ccea;
      color: #fff;
    }
    .storages_tab input:hover {
      background-color: #015aaa;
    }
    .img_list {
      display: inline-block;
      vertical-align: middle;
      background: url('/iwaterTest/css/image/shopping-list.png') no-repeat center;
      width: 20px;
      height: 20px;
      margin-right: 15px;
      color: #fff;
      background-color: #74ccea;
      padding: 10px;
      border-radius: 20px;
    }
    .back_button {
      float: right;
      padding-top: 10px;
      font-size: 16px;
    }
    .back_button a {
      color: #000;
      text-decoration: none;
    }

    .back_button:hover .img_list{
      background-color: #015aaa;
    }

    <?php if (isset($_GET['id'])) { echo '#gbox_list_storage'; } else { echo '#gbox_list_info_storage'; }?> {
      display: none;
   }
</style>
