<div class="main">
   <div class="category_label">
      История трансакций
      <div class="back_button"><a href="/iwaterTest/admin/storage_info"> <span class="img_list"></span> К списку складов</a></div>
      <hr color="#e3e9f1" size="1px" width="100%">
   </div>
  <div class="search">
   <form>
      <table class="input_form">
         <tr>
            <td style="width: 70px;">Дата с</td>
            <td style="width: 150px;"><input type="text" id="from" name="from" value="<?php if (isset($_GET['from'])) echo date('d/m/y', $_GET['from']); ?>"></td>
            <td> по </td>
            <td style="width: 150px;"><input type="text" id="to" name="to" value="<?php if (isset($_GET['to'])) echo date('d/m/y', $_GET['to']); ?>"></td>
         </tr>
         <tr>
            <td>Склад</td>
            <td style="width: 150px;">
               <select class="storage" id="storage" name="storage" style="width: 100%">
                  <option value="" style="font-style: italic;" selected>По всем складам</option>
               </select>
               </td>
         </tr>
         <tr>
            <td>Товар</td>
            <td style="width: 150px;">
               <select class="unit" id="unit" name="unit" style="width: 100%">
                  <option value="" style="font-style: italic;" selected>По всем товарам</option>
               </select>
            </td>
         </tr>
         <tr>
            <td></td><td></td><td></td><td></td> <!-- Заполняет строку, чтобы сдвинуть кнопки вправо -->
            <td><input type="button" class="classic" name="transaction_info_upd" value="Фильтр" onclick="setFilter();" placeholder="Дата (d/m/Y)" style="width: 100px; height: 25px;"></td>
            <td><input type="button" class="classic" name="transaction_info_reset" value="Сбросить" onclick="refreshFilter();" placeholder="Дата (d/m/Y)" style="width: 100px; height: 25px;"></td>
         </tr>
      </table>
   </form>
</div>
<table id="list"></table>
<div id="pager"></div>
<div class="custom_paginator_conteiner" style="width: 985px;">
   <div class="custom_paginator" style="display: inline-block; float: right; padding: 3px 10px 0 726px;"></div>
</div>
</div>

<?php
   /*if (isset($_GET['transaction_info_reset'])) {
      echo "<script>location.replace('/iwaterTest/admin/transaction_info?transaction_info_upd')</script>";
   } else {
     if (isset($_GET['transaction_info_upd'])) {
        $getDate .= "transaction_info_upd=";
        if(isset($_GET['unit'])){
          $getDate .= "&unit=".$_GET['unit'];
        }
        if (isset($_GET['storage'])) {
          $getDate .= "&storage=" . $_GET['storage'];
       }
        if (isset($_GET['from'])) {
            $getDate .= "&from=".$_GET['from'];
        }
        if (isset($_GET['to'])) {
            $getDate .= "&to=".$_GET['to'];
        }
      }
   }*/
?>

<script type="text/javascript">
   $("#list").jqGrid({
       url: "/iwaterTest/backend.php?<?echo $getDate;?>",
       datatype: "xml",
       mtype: "POST",
       xmlReader: {
          root:"rows",
          total:"total",
          records:"rows>records",
          repeatitems:true
       },
       colNames: ["Товар", "Склад", "Тип трансакции", "Количество", "Комментарий", "Дата"],
       colModel: [
          {name: "unit", index: "unit", width: 270, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
          {name: "storage", index: "storage", width: 160, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
          {name: "type", index: "type", width: 110, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
          {name: "count", index: "count", width: 90, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
          {name: "comment", index: "comment", width: 230, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}},
          {name: "date", index: "date", width: 90, align: "center", sorttype: 'string', search: true, editable: false, searchoptions: {sopt: ['cn', 'eq', 'ne', 'bw', 'bn', 'ew', 'en', 'nc', 'nu', 'nn', 'in', 'ni']}}
       ],
       pager: "#pager",
       rowNum: 30,
       rowList: [30, 50, 100],
       sortname: 'date',
       viewrecords: true,
       sortorder: "desc",
       onPaging: function () {},
       onSortCol: function () {},
       loadComplete: function () {
          createPager();
       },
       onSelectRow: function (id) {   },
       editurl: "/iwaterTest/backend.php",
       gridview: true,
       autoencode: false,
       caption: "История трансакций",
       loadonce: false,
       sortable: true
   });
   setFilter();
   $("#list").jqGrid('navGrid', "#pager", {edit: false, add: false, del: false}, {}, {}, {}, {
      multipleSearch: true,// Поиск по нескольким полям
      multipleGroup: true, // Сложный поиск с подгруппами условий
      showQuery: true
   });

   $("#list").jqGrid('setGridHeight', 'auto');
      $("#unit").onclick = function(){
   // sel = document.getElementById('unit').value;
    log.console('SELCET');
   };

     $(document).ready(function () {
                        $("#datepicker").datepicker({
                                changeMonth: true,
                                changeYear: true,
                                showButtonPanel: true,
                                beforeShow: function (input, inst) {
                                    var rect = input.getBoundingClientRect();
                                    setTimeout(function () {
                                        inst.dpDiv.css({top: rect.top + 25, left: rect.left - 95});
                                    }, 0);
                                }
                            },
                            $.datepicker.regional["ru"]);
                        $("#datepicker").datepicker("option", "dateFormat", "dd/mm/yy");
                    });

                    $( function() {
                        var dateFormat = "mm/dd/yy",
                            from = $( "#from" )
                                .datepicker({
                                    defaultDate: "+1w",
                                    changeMonth: true,
                                    numberOfMonths: 1
                                })
                                .on( "change", function() {
                                    to.datepicker( "option", "minDate", getDate( this ) );
                                }),
                            to = $( "#to" ).datepicker({
                                defaultDate: "+1w",
                                changeMonth: true,
                                numberOfMonths: 1
                            })
                                .on( "change", function() {
                                    from.datepicker( "option", "maxDate", getDate( this ) );
                                });

                        function getDate( element ) {
                            var date;
                            try {
                                date = $.datepicker.parseDate( dateFormat, element.value );
                            } catch( error ) {
                                date = null;
                            }

                            return date;
                        }
                        $("#from, #to").datepicker("option", "dateFormat", "dd/mm/yy");
                        $("#from").val("<?php echo date('d/m/y', $_GET['from']) ?>");
                        $("#to").val("<?php echo date('d/m/y', $_GET['to']) ?>");
                    } );

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
            if (<?php if(isset($_GET['unit']) && $_GET['unit'] != "") { echo $_GET['unit']; } else { echo 0;}?> == unit_list[i]['id']) {
               $('<option>', { id: i, value: unit_list[i]['id'], text: unit_list[i]['name'], selected: 'selected'}).appendTo('#unit');
            } else {
               $('<option>', { id: i, value: unit_list[i]['id'], text: unit_list[i]['name']}).appendTo('#unit');
            }
         }
      }
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
         }

         $('#storage').val(<?php if (isset($_GET['storage'])) { echo "'" . $_GET['storage'] . "'"; }?>);
      }
   });

function setFilter(){
    console.log($('#unit').val());
    $.ajax({
      type:'POST',
      url:'/iwaterTest/backend.php?transaction_info_upd=&unit='+ <?php echo $_GET['unit'] ?> +'&storage='+ <?php echo $_GET['storage'] ?> ,
      data:{
        },
        response:'text',
         success:function (data) {
          sel = $('#unit').val();
         // table_data = JSON.parse(data);
          $('#list').jqGrid('clearGridData');
          $('#list').jqGrid('setGridParam', {url:'/iwaterTest/backend.php?transaction_info_upd=&unit='+ sel +'&storage='+ <?php echo $_GET['storage'] ?> +'&from='+ $('#from').val() +'&to='+ $('#to').val()});
          $('#list').trigger('reloadGrid');
         }
});
}

   function refreshFilter() {
      window.location.href = "/iwaterTest/admin/transaction_info?storage="+ <?php echo $_GET['storage'] ?>+'&from=&to=';
   }

   function createPager() {
      var currentPage = $("#list").getGridParam('page');
      var maxPage = $("#list").getGridParam('lastpage');

      var start = currentPage < 3 ? 1 : currentPage - 2;
      var finish = currentPage + 2 <= maxPage ? currentPage + 2 : maxPage;
      var html = '<input type="button" name="" value="<" onclick="goToPage(' + (currentPage - 1) + ');">'; // Строка с пагинотором

      for (var i = start; i <= finish; i++) {
         html += '<input type="button" name="" value="' + i + '" onclick="goToPage(' + i + ')">';
      }

      html += '<input type="button" name="" value=">" onclick="goToPage(' + (currentPage + 1) + ')">';
      $('.custom_paginator').html(html);
   }

   function goToPage(page) {
      $("#list").trigger("reloadGrid",[{page:page}]);
   }
</script>

<style>
   .main {
      display: grid;
   }

   .category_label {
      float: left;
      font-size: 21px;
      padding: 10px 0 0px 25px;
   }

   .classic {
      max-height: 30px;
      background-color: #0157a4;
      color: #fff;
      border-radius: 20px;
      cursor: pointer;
   }

   .input_form {
      background-color: #fff;
      border-radius: 7px;
      padding: 10px;
      margin-bottom: 10px;
   }

   .custom_paginator input {
      width: 26px;
      height: 27px;
      margin-left: 5px;
      background-color: #fff;
   }

   /*Таблица jqgrid*/
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

   .reset_button:active {
      padding: 2px 30px;
   }

   .s-ico {
      display: none !important;
   }

   .ui-jqgrid-htable ui-common-table {
      width: 100%;
   }

   .ui-jqgrid .ui-jqgrid-bdiv {
      height: 400px !important;
      /* width: 1020px !important; */
   }

   .ui-jqgrid .ui-jqgrid-view,
   .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
      /* width: 1020px !important; */
   }

   .ui-state-default, .ui-widget-content .ui-state-default {
      /* width: auto !important; */
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
      padding-bottom: 10px;
      font-size: 16px;
   }

   .back_button a {
      color: #000;
      text-decoration: none;
   }

   .back_button:hover .img_list {
      background-color: #015aaa;
   }
</style>
