<div class="main">
   <img src="/iwaterTest/css/image/loading-gallery.gif" id="loading" class="loading" alt="" style="width: 141px; display: none;z-index: 9999"/>
   <div class="clients_list_table">
      <div class="name_title">
         <div class="name_position">Необработанные заказы <span class="tab"><?php echo get_count_unchecked_order(); ?></span></div>
      </div>
   </div>
   <div class="search">
      <form>
         <label for="from" style="line-height: 30px; margin-right: 30px;">Искать по</label><select class="edit_role" name="role" style="height: 23px; min-width: 150px; padding-left: 5px;">
            <option value="3" selected>id клиента</option>
            <option value="5">Клиент</option>
            <option value="5">Адрес</option>
            <option value="5">Дата</option>
            <option value="5">Время</option>
            <option value="5">Заказ</option>
         </select>

         <label style="line-height: 30px; margin: 0 30px;">Данные</label><input class="data" name="" type="text">
         <input class="search_button" type="button" value="Поиск" onclick="">
         <input class="reset_button" type="button" value="Сброс" onclick="">
         <input id="order_excel" type="button" class="classic reset_button" value="Выгрузить в эксель" onclick="load_excel();">
      </form>
   </div>

   <div class="inline-block form_status" style="display: none;">
      <label for="status" id="label_status">Статус выделeнных строк</label>
      <div class="inline-block status_list">
         <select id="status" name="status">
            <option value="0" selected>Отправлен</option>
            <option value="1">Принят</option>
            <option value="2">Подтверждён</option>
            <option value="3">Доставлен</option>
         </select>
         <input type="button" value="Изменить" onclick="change_statuses(this)">
      </div>
   </div>
   <table id="list"></table>
   <div id="pager"></div>

   <div class="custom_paginator" style="display: inline-block; float: right; padding: 3px 10px 0px 110px;"></div>
   <input class="classic multi search_button"  type="hidden" value="Мультивыделение" placeholder="Дата путевого (d/m/Y)">

   <a href="/iwaterTest/admin/logs?logs=order" style="color: #000; float: right; margin: 5px 60px 5px 20px;">Журнал операций</a>
   <input id="all_order_excel" type="button" class="classic btn_link" value="Выгрузить базу данных" onclick="all_load_excel();" style="float: right;">

   <div id="overlay"></div><!-- Пoдлoжкa -->

   <?php
      $getDate="";

      if(isset($_GET['list_order_upd'])) {
         $getDate ="&list_order_upd=";
         if (isset($_GET['from'])) {
            $getDate .="&from=".$_GET['from'];
         }
         if (isset($_GET['to'])) {
            $getDate .="&to=".$_GET['to'];
         }
      }
   ?>

   <?php
      if(isset($_GET['client_order'])) {
         $get = "order_app=order&client_order=".$_GET['client_order'];
      } elseif (isset($_GET['no_date_order'])) {
         $get = "order_app=order&no_date_order=".$_GET['no_date_order'];
      } else {
         $get = "order_app=order";
      };
   ?>
   <script type="text/javascript">
   $(function() {
      var events, originalReloadGrid, $grid = $("#list"),
         multiselect = false,
         enableMultiselect = function(isEnable) {
            $(this).jqGrid('setGridParam', {
               multiselect: (isEnable ? true : false)
            });
         };
      var lastsel = 0;

      $("#list_units").jqGrid({
         mtype: "POST",
         url: '/iwaterTest/backend.php?list_units',
         datatype: "xml",

         colNames: ['№', 'Товар'],
         colModel: [
            { name: 'id', key: true, index: 'id', width: 55, align: "center", sortable: false },
            { name: 'name', index: 'name', align: "center", width: 300, sortable: false }
         ],
         viewrecords: true,
         caption: 'Список товаров',
         sortable: false,
         shrinkToFit: false
      });

      $("#list").jqGrid({
         url: "/iwaterTest/backend.php?<?echo $get.$getDate?>",
         datatype: "xml",
         mtype: "POST",
         xmlReader: {
            root: "rows",
            total: "total",
            records: "rows>records",
            repeatitems: true
         },
         colNames: ["", "ID заказа", "Клиент", "Адрес", "Дата", "Время", "Примечание", "Заказ", "Статус", "Система", ""],
         colModel: [
            { name: "edit", width: 50, formatter: acceptOrder },
            { name: "id", index: "id", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni'] } },
            {
               name: "client_id",
               index: "client_id",
               width: 80,
               align: "center",
               editable: true,
               sorttype: 'string',
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "address",
               index: "address",
               width: 120,
               align: "center",
               sorttype: 'string',
               search: true,
               editable: true,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "date",
               index: "date",
               width: 110,
               align: "center",
               editable: false,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "period",
               index: "period",
               width: 100,
               align: "center",
               editable: true,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "notice",
               index: "notice",
               width: 120,
               align: "center",
               editable: true,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "water_equip",
               index: "water_equip",
               width: 210,
               align: "center",
               editable: true,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: 'status',
               index: "status",
               width: 90,
               align: "center",
               edittype: 'select',
               formatter: 'select',
               editoptions: {
                  value: {
                     0: 'Отправлен',
                     1: 'Принят',
                     2: "Подтверждён",
                     3: "Доставлен"
                  }
               },
               editable: true,
               searchoptions: {
                  sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']
               }
            },
            {
               name: "system",
               index: "system",
               width: 50,
               align: "center",
               editable: false,
               formatter: showIconOS
            },
            {
               name: "checked",
               index: "checked",
               width: 50,
               align: "center",
               editable: false,
               hidden: true
            },
         ],
         pager: "#pager",
         rowNum: 30,
         rowList: [30, 50, 100],
         sortname: 'id',
         viewrecords: true,
         sortorder: "desc",
         multiselect: true,
         onPaging: function() {
            enableMultiselect.call(this, true);
         },
         onSortCol: function() {
            enableMultiselect.call(this, true);
         },
         loadComplete: function() {
            createPager();
            //                                if(typeof $("#list td:nth-child(2)")[1] != "undefined") {
            //                                    if ($("#list td:nth-child(2)")[1].innerText != "") {
            //
            ////                                        $("#list td:nth-child(1)").show();
            ////                                        $("#list tr:nth-child(1) td:nth-child(1)").hide();
            ////                                        $("#list tr:nth-child(1)").append('<td style="width:0px">New Column</td>');
            //                                        // Если вы задумались о том, зачем этот код, то я поведую вам страшную историю, как jqgrid при изменениях работы с мультивыделения
            //                                        // на одиночное выставлял первую строку в таблицы в display:none без причины при поиске. Так вот здесь стоит костыль,
            //                                        // вдруг оказывается client id, то я вручную отображаю скрытый зачем-то нулевой столбец
            //                                    }
            //                                }
            if (!multiselect) {
               $(this).jqGrid('hideCol', 'cb');
            } else {
               $(this).jqGrid('showCol', 'cb');
            }
            //                                enableMultiselect.call(this, multiselect);

         },

         gridComplete: function() {
            var ids = $("#list").jqGrid('getDataIDs');
            for (var i = 0; i < ids.length; i++) {
               var cl = ids[i];

               be = "<input style='height:22px;' type='button' value='Edit' onclick=\"window.location.href='editItem.asp?ID=10'\"  />";

               $("#list").jqGrid('setRowData', ids[i], {
                  act: be
               });
            }
         },
         onSelectRow: function(id) {
            var multi_flag = $(".multi_hidden").val() == "multi";
            if (multi_flag == true) {
               var div_date = $("#change_date");
               div_date.detach();
               var tr = $("#" + id);
               var td = tr.find("td")[2];
               var top = td.offsetTop + 17;
               var left = td.offsetLeft + 400;
               var html = '<div id="change_date" type="text" role="textbox" class="editable inline-edit-cell ui-widget-content ui-corner-all" style="border-width: 2px;	border-color: #cdcdcd; z-index: 9999; position: absolute;width: 115px;height: 17px; left:' + left + 'px; top:' + top + 'px;"><div><a id="modal_link" href="#">Перенести клиента</a></div></div>';
               tr.append(html);

               var div_date = $("#edit_order");
               div_date.detach();
               var tr = $("#" + id);
               //                                    tr.setAttribute("width", "200");
               var td = tr.find("td")[2];
               var top = td.offsetTop;
               var left = 0;




               $("#date_client_id")[0].value = id;
               $("#datepicker")[0].value = tr.find("td")[6].innerHTML;
               //                                $("#time")[0].value = tr.find("td")[4].innerHTML;
               $('a#modal_link').click(function(event) { // лoвим клик пo ссылки с id="go"
                  event.preventDefault(); // выключaем стaндaртную рoль элементa
                  $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                     function() { // пoсле выпoлнения предъидущей aнимaции
                        $('#modal_form')
                           .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                           .animate({
                              opacity: 1,
                              top: '50%'
                           }, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                     });
               });
               /!* Зaкрытие мoдaльнoгo oкнa, тут делaем тo же сaмoе нo в oбрaтнoм пoрядке *!/
               $('#modal_close, #overlay').click(function() { // лoвим клик пo крестику или пoдлoжке
                  $('#modal_form')
                     .animate({
                           opacity: 0,
                           top: '45%'
                        }, 200, // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
                        function() { // пoсле aнимaции
                           $(this).css('display', 'none'); // делaем ему display: none;
                           $('#overlay').fadeOut(400); // скрывaем пoдлoжку
                        }
                     );
               });
               if (id && id !== lastsel && lastsel != 0) {
                  jQuery('#list').jqGrid('saveRow', lastsel);
                  jQuery('#list').jqGrid('editRow', id, true);
                  lastsel = id;
               } else {
                  if (lastsel == 0) {
                     jQuery('#list').jqGrid('editRow', id, true);
                     lastsel = id;
                  }
               }
            }
         },
         editurl: "/iwaterTest/backend.php?app",
         gridview: true,
         autoencode: false,
         caption: "Заказы",
         loadonce: false,
         sortable: true,
         multiselect: true,
         rowattr: function(cellvalue, options, rowObject) {
            if (cellvalue.checked == 0) {
               return { 'class': 'non_checked_order' };
            } // Если заказ не был обработан, его нужно выделить
         }
      });


      events = $grid.data("events"); // read all events bound to
      // Verify that one reloadGrid event handler is set. It should be set
      if (events && events.reloadGrid && events.reloadGrid.length === 1) {
         originalReloadGrid = events.reloadGrid[0].handler; // save old
         $grid.unbind('reloadGrid');
         $grid.bind('reloadGrid', function(e, opts) {
            enableMultiselect.call(this, true);
            originalReloadGrid.call(this, e, opts);
         });
      }

      $(".multi").button().click(function() {
         var $this = $(this);


         multiselect = $('.multi_hidden').val() == 'multi';
         multiselect ? $('.multi_hidden').val('one') : $('.multi_hidden').val('multi');
         multiselect ? $(".form_status").show() : $(".form_status").hide();
         $this.button("option", "label", multiselect ?
            "Редактирование таблицы" :
            "Мультивыделение");
         enableMultiselect.call($grid[0], true);
         $grid.trigger("reloadGrid");
      });
      $("#list").jqGrid('navGrid', "#pager", {
         edit: false,
         add: false,
         del: true
      }, {}, {}, {}, {
         multipleSearch: true, // Поиск по нескольким полям
         multipleGroup: true, // Сложный поиск с подгруппами условий
         showQuery: true
      });

      $("#list").navButtonAdd('#pager', {
         buttonicon: "",
         title: "Оформить",
         caption: "Оформить",
         position: "last",
         onClickButton: correctOrder
      });

      $("#list").jqGrid('filterToolbar', {
         searchOperators: true
      });
      $("#list").jqGrid('setGridHeight', 300);
   });

   $(document).ready(function() {
      createPager();
      $("#datepicker").datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            beforeShow: function(input, inst) {
               var rect = input.getBoundingClientRect();
               setTimeout(function() {
                  inst.dpDiv.css({
                     top: rect.top + 25,
                     left: rect.left - 95
                  });
               }, 0);
            }
         },
         $.datepicker.regional["ru"]);
      $("#datepicker").datepicker("option", "dateFormat", "dd/mm/yy");

      $("#no_date_order").click(function() {
         if ($(this).is(":checked")) {
            window.location = "/iwaterTest/admin/list_orders_app?no_date_order=1";
         } else {
            window.location = "/iwaterTest/admin/list_orders_app";

         }
      });
   });

   $(function() {
      var dateFormat = "mm/dd/yy",
         from = $("#from")
         .datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
         })
         .on("change", function() {
            to.datepicker("option", "minDate", getDate(this));
         }),
         to = $("#to").datepicker({
            defaultDate: "+1w",
            changeMonth: true,
            numberOfMonths: 1
         })
         .on("change", function() {
            from.datepicker("option", "maxDate", getDate(this));
         });

      function getDate(element) {
         var date;
         try {
            date = $.datepicker.parseDate(dateFormat, element.value);
         } catch (error) {
            date = null;
         }

         return date;
      }
      $("#from, #to").datepicker("option", "dateFormat", "dd/mm/yy");
      $("#from").val("<?php echo $_GET['from'] ?>");
      $("#to").val("<?php echo $_GET['to'] ?>");
   });

   function correctOrder() {
      var selectedRowId = $('#list').jqGrid('getGridParam', 'selrow');
      var cellValue = $('#list').jqGrid('getCell', selectedRowId, 'id');

      if (cellValue) {
         location.href = "/iwaterTest/admin/add_order?migrate_id=" + cellValue;
      } else {
         alert("Нет выделeнных строк");
      }
   }

   function load_excel() {
      var from = $("#from").val();
      var to = $("#to").val();
      if (from == "" || to == "") {
         $("#error_excel").show();
         return 0;
      }
      $("#error_excel").hide();
      $('#loading').show();
      $('#order_excel').hide();
      $('#all_order_excel').hide();
      $.ajax({
         type: "POST",
         data: {
            from: from,
            to: to,
            order_app_excel_file: ""
         },
         url: "/iwaterTest/backend.php",
         success: function(file_name) {
            //                                location.href = '/iwaterTest/files'+'.xlsx';
            from = from.split("/");
            to = to.split("/");
            location.href = '/iwaterTest/files/order_from_' + from[0] + from[1] + from[2] + '_to_' + to[0] + to[1] + to[2] + '.xlsx';
            $('#loading').hide();
            $('#order_excel').show();
            $('#all_order_excel').show();
         }
      });
   }

   // Кастомный пагинотор с методом отрисовки кнопок и переходами в гриде
   function createPager() {
      var currentPage = $("#list").getGridParam('page');
      var start = currentPage < 3 ? 1 : currentPage - 2;
      var selectCurrentPage = '';
      var html = '<input type="button" name="" value="<" onclick="goToPage(' + (currentPage - 1) + ');">'; // Строка с пагинотором

      for (var i = start; i <= start + 5; i++) {
         selectCurrentPage = i == currentPage ? ' style="background-color: #74ccea; color: #fff;" ' : '';
         html += '<input type="button" name="" value="' + i + '" onclick="goToPage(' + i + ')" ' + selectCurrentPage + ' >';
      }

      html += '<input type="button" name="" value=">" onclick="goToPage(' + (currentPage + 1) + ')">';
      $('.custom_paginator').html(html);
   }

   function goToPage(page) {
      $("#list").trigger("reloadGrid", [{
         page: page
      }]);
   }

   function all_load_excel() {
      $('#loading').show();
      $('#order_excel').hide();
      $('#all_order_excel').hide();
      $.ajax({
         type: "POST",
         data: {
            from: "",
            to: "",
            order_app_excel_file: "all"
         },
         url: "/iwaterTest/backend.php",
         success: function(file_name) {
            location.href = '/iwaterTest/files/all_order.xlsx';
            $('#loading').hide();
            $('#order_excel').show();
            $('#all_order_excel').show();
         }
      });
   }

   function showIconOS(cellvalue, options, rowObject) {
      if (typeof cellvalue != 'undefined') {
         var icon = cellvalue.split('-');
         var html = "<img src='/iwaterTest/css/image/" + icon[0] + ".png' title='version:" + icon[1] + "'/>";
         return html;
      } else {
         return 'пусто';
      }
   }

   function acceptOrder(cellvalue, options, rowObject) {
      return '<a href="/iwaterTest/admin/add_order?migrate_id=' + options['rowId'] + '"><img src="/iwaterTest/css/image/edit.png" style="padding: 0 20px;"></a>';
   }

   function change_statuses() {
      var array_id;
      var status = $("#status").val();
      array_id = jQuery("#list").jqGrid('getGridParam', 'selarrrow');

      $.ajax({
         type: "POST",
         data: {
            statuses_app_list: array_id,
            status: status
         },
         url: "/iwaterTest/backend.php",
         success: function(file_name) {
            location.href = '/iwaterTest/admin/list_orders_app';
         }
      });
   }
   </script>

    <style type="text/css">
        .s-ico {
            display: none !important;
        }
        .ui-jqgrid .ui-jqgrid-pager,
        .ui-jqgrid .ui-jqgrid-caption {
            display: none !important;
        }
        .btn_link {
            border: none;
            background-color: #fff0;
            cursor: pointer;
        }
        .btn_link:hover {
            color: #015aaa;
        }
        .btn_link:focus {
            color: #015aaa;
        }
        .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all,
        .ui-jqgrid-hbox {
            display: none;
        }

        .search_button {
           border: 2px solid #015aaa !important;
           padding: 2px 30px !important;
        }

         .search_button:active {
            background-color: #015aaa;
            border: 2px solid #015aaa;
         }

         /*локальные стили*/
         .ui-jqgrid-bdiv {
            height: 376px !important;
            margin-bottom: 30px !important;
         }

         .name_position>.tab {
            color: #fff;
            font-weight: 400;
            border-radius: 6px;
            padding: 1px 9px;
            font-size: 14px;
            background-color: #ef081f;
         }

         .custom_paginator {
            display: flex;
            float: right;
            padding: 0px 0px 0px 110px;
            margin: -9px -45px 0 0;
         }
         .custom_paginator input {
            width: 26px;
            height: 27px;
            margin-left: 5px;
            background-color: #fff;
         }
         .non_checked_order {
            background: #c3edfb !important;
         }
    </style>
