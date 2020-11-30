<div class="main">
    <img src="/iwaterTest/css/image/loading-gallery.gif" id="loading" class="loading" alt="" style="width: 141px; display: none;z-index: 9999"/>
    <div class="clients_list_table">
        <div class="search">
            <div>Поиск по дате</div>
            <form>
                <label for="from">Дата с</label>
                <input type="text" id="from" name="from" value="<?php echo $_GET['from']?>">
                <label for="to">По</label>
                <input type="text" id="to" name="to" value="<?php echo $_GET['to']?>">
                <input type="submit" class="classic" value="Фильтр" placeholder="Дата путевого (d/m/Y)"">
                <input id="order_excel" type="button" class="classic" value="Выгрузить в эксель" onclick="load_excel();"">
                <label id="error_excel" style="color:red" hidden> Временные рамки не заданы </label>

                <input name="list_order_upd" type="hidden">

            </form>
        </div>
        <div class="inline-block">
            <input type="button" class="classic multi"  value="Мультивыделение" placeholder="Дата путевого (d/m/Y)"">
             <input name="multi_hidden" class="multi_hidden" type="hidden" value="multi">
        </div>
        <div class="inline-block form_status" style="display: none;">
            <label for="status" id="label_status">Статус выделeнных строк</label>
            <div class="inline-block status_list">
                      <select id="status" name="status">
                        <option value="0" selected>В работе</option>
                        <option value="1">Отмена</option>
                        <option value="2">Доставлен</option>
                        <option value="3">Перенос</option>
                    </select>
                <input type="button" value="Изменить" onclick="change_statuses(this)">
            </div>
        </div>
        <table id="list">

        </table>
        <div id="pager">

        </div>
        <input id="all_order_excel" type="button" class="classic" value="Выгрузить базу данных" onclick="all_load_excel();"">


        <div>
            <div>
                <a href="/iwaterTest/admin/logs?logs=order">Посмотреть журнал операций</a>
                <input class="classic" id="no_date_order" name="no_date_order" type="checkbox" <?php if(isset($_GET['no_date_order'])) echo "checked" ?>> Отобразить заказы без даты
            </div>

			<!--  Таблица с товарами  -->
			<table id="list_units" style = "min-width: 283px;"></table>

            <div id="modal_form"><!-- Сaмo oкнo -->
                <span id="modal_close">X</span> <!-- Кнoпкa зaкрыть -->
                <div class="title_date">Дата новой доставки</div>
                <form id="form_datepicker" method="post" action="/iwaterTest/backend.php">
                    <input id="date_client_id" name="id" type="hidden">
                    <p class="datepicker_p"><label for="datepicker">Дата: </label><input name="date" type="text"
                                                                                         id="datepicker"></p>

                    <div id="datepicker_submit">
                        <input id="submit" name="submit" type="submit" value="Сохранить изменения">
                    </div>
                    <input name="change_date" type="hidden">
                </form>

            </div>
            <div id="overlay"></div><!-- Пoдлoжкa -->
            <div>
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

                <script type="text/javascript">
                    $(function () {
                        var events, originalReloadGrid, $grid = $("#list"), multiselect = false,
                            enableMultiselect = function (isEnable) {
                                $(this).jqGrid('setGridParam', {multiselect: (isEnable ? true : false)});
                            };
                        var lastsel = 0;

                        $("#list").jqGrid({
                            url: "/iwaterTest/backend.php?driver_control",
                            datatype: "xml",
                            mtype: "POST",
							xmlReader: {
								root:"rows",
								total:"total",
								records:"rows>records",
								repeatitems:true
							},
                            colNames: ["","id Заказа","Водитель", "Время доставки", "Просрочено", "Координаты заказчика", "Координаты водителя", "", "Комментарий", "Тара"],
                            colModel: [
                                {name:"edit", width:22},
                                {name: "order_id", index: "order_id", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "driver_id", index: "driver_id", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "date", index: "date", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "overdue", index: "overdue", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "order_cord", index: "order_cord", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "driver_cord", index: "driver_cord", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "violation", index: "violation", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "motice", index: "notice", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}},
                                {name: "tanks", index: "tanks", width: 80, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: {sopt: ['eq', 'ne', 'bw', 'bn', 'ew', 'en', 'cn', 'nc', 'nu', 'nn', 'in', 'ni']}}
							],
							pager: "#pager",
                            rowNum: 30,
                            rowList: [30, 50, 100],
							sortname: 'date',
                            viewrecords: true,
							sortorder: "desc",
                            onPaging: function () {
                                enableMultiselect.call(this, true);
                            },
                            onSortCol: function () {
                                enableMultiselect.call(this, true);
                            },
                            loadComplete: function () {
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
                            onSelectRow: function (id) {
                                var multi_flag = $(".multi_hidden").val()=="multi";
                                if(multi_flag == true) {
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
                                    var html = '<a href="/iwaterTest/admin/edit_orders?id=' + id + '"><div id="edit_order" type="text" role="textbox" class="editable inline-edit-cell ui-widget-content ui-corner-all" style="    background: url(../../css/image/edit.png) 0 0 no-repeat;background-size: contain; z-index: 9999; position: absolute;width: 18px;height: 13px; left:' + left + 'px; top:' + top + 'px;"></div></a>';
                                    tr.append(html);




                                    $("#date_client_id")[0].value = id;
                                    $("#datepicker")[0].value = tr.find("td")[6].innerHTML;
//                                $("#time")[0].value = tr.find("td")[4].innerHTML;
                                    $('a#modal_link').click(function (event) { // лoвим клик пo ссылки с id="go"
                                        event.preventDefault(); // выключaем стaндaртную рoль элементa
                                        $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                                            function () { // пoсле выпoлнения предъидущей aнимaции
                                                $('#modal_form')
                                                    .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                                                    .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                                            });
                                    });
                                    /!* Зaкрытие мoдaльнoгo oкнa, тут делaем тo же сaмoе нo в oбрaтнoм пoрядке *!/
                                    $('#modal_close, #overlay').click(function () { // лoвим клик пo крестику или пoдлoжке
                                        $('#modal_form')
                                            .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
                                                function () { // пoсле aнимaции
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
                            editurl: "/iwaterTest/backend.php",
                            gridview: true,
                            autoencode: false,
                            caption: "Заказы",
                            loadonce: false,
                            sortable: true,
                            multiselect: true
                        });
                        events = $grid.data("events"); // read all events bound to
// Verify that one reloadGrid event handler is set. It should be set
                        if (events && events.reloadGrid && events.reloadGrid.length === 1) {
                            originalReloadGrid = events.reloadGrid[0].handler; // save old
                            $grid.unbind('reloadGrid');
                            $grid.bind('reloadGrid', function (e, opts) {
                                enableMultiselect.call(this, true);
                                originalReloadGrid.call(this, e, opts);
                            });
                        }
                        $(".multi").button().click(function () {
                            var $this = $(this);


                            multiselect = $('.multi_hidden').val()=='multi';
                            multiselect ? $('.multi_hidden').val('one') : $('.multi_hidden').val('multi');
                            multiselect ? $(".form_status").show() : $(".form_status").hide();
                            $this.button("option", "label", multiselect ?
                                "Редактирование таблицы" :
                                "Мультивыделение");
                            enableMultiselect.call($grid[0], true);
                            $grid.trigger("reloadGrid");
                        });
                        $("#list").jqGrid('navGrid', "#pager", {edit: false, add: false, del: true}, {}, {}, {},
						{
							multipleSearch: true,// Поиск по нескольким полям
							multipleGroup: true, // Сложный поиск с подгруппами условий
							showQuery: true
						});

                        $("#list").jqGrid('filterToolbar', {searchOperators: true});
                        $("#list").jqGrid('setGridHeight', 300);
//                        $("#list").jqGrid('hideCol', 'cb');
//                        $("#list").jqGrid('setGridParam', {multiselect: false});
//                        $("#list").jqGrid('hideCol', 'cb');
//                        $("#list").jqGrid('showCol', 'edit');
//                        $("#list").jqGrid('setGridParam', {multiselect: false});
//                        $("#list").trigger("reloadGrid")


                    });

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

                        $("#no_date_order").click(function(){
                            if($(this).is( ":checked" )){
                                window.location= "/iwaterTest/admin/list_orders?no_date_order=1";
                            }else{
                                window.location= "/iwaterTest/admin/list_orders";

                            }
                        });
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
                        $("#from").val("<?php echo $_GET['from'] ?>");
                        $("#to").val("<?php echo $_GET['to'] ?>");
                    } );

                    function load_excel(){
                        var from = $("#from").val();
                        var to =  $("#to").val();
                        if(from == "" || to == ""){
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
                                order_excel_file: ""
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
//                                location.href = '/iwaterTest/files'+'.xlsx';
                                from = from.split("/");
                                to = to.split("/");
                                location.href = '/iwaterTest/files/order_from_'+from[0]+from[1]+from[2]+'_to_'+to[0]+to[1]+to[2]+'.xlsx';
                                $('#loading').hide();
                                $('#order_excel').show();
                                $('#all_order_excel').show();
                            }
                        });
                    }

                    function all_load_excel(){
                        $('#loading').show();
                        $('#order_excel').hide();
                        $('#all_order_excel').hide();
                        $.ajax({
                            type: "POST",
                            data: {
                                from: "",
                                to: "",
                                order_excel_file: "all"
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
                                location.href = '/iwaterTest/files/all_order.xlsx';
                                $('#loading').hide();
                                $('#order_excel').show();
                                $('#all_order_excel').show();
                            }
                        });
                    }

                    function change_statuses(){
                        var array_id;
                        var status = $("#status").val();
                        array_id = jQuery("#list").jqGrid('getGridParam','selarrrow');

                        $.ajax({
                            type: "POST",
                            data: {
                                statuses_order_list: array_id,
                                status: status
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (file_name) {
                                location.href = '/iwaterTest/admin/list_orders';
                            }
                        });
                    }
                </script>
