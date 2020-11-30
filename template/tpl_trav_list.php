<div class="main">
    <div class="main_form">
        <!-- АНИМАЦИЯ ЗАКГРУЗКИ -->
        <img src="../../css/image/loading-gallery.gif" class="loading" alt="" style="width: 100px; top: 50%; left: 50%; display: none;"/>
        <!-- ПУНКТ СПИСКА ПУТЕВЫХ ЛИСТОВ  -->
        <div class="category_label">
            Список путевых листов
        </div>
        <div class="list_lists">
            <div class="list_lists_date">
                <p>С</p>
                <input type="text" class="list_lists_from" name="" value="<?php echo $_GET['from']; ?>" placeholder="Начальная дата" style="margin-left: 35px;">
                <p style="margin-left: -20px !important;">по</p>
                <input type="text" class="list_lists_to" name="" value="<?php echo $_GET['to']; ?>" placeholder="Конечная дата" style="margin-left: 35px;">
                <input type="submit" class="lists_filter" name="lists_filter" onclick="confirmOutputPeriod();" value="Фильтр">
            </div>
            <table class="main_table" id="list_lists_table"></table>
            <div id="list_lists_paginator"></div>
        </div>

        <!-- ДОБАВИТЬ ПУТЕВОЙ ЛИСТ -->
        <div class="category_label">
            Добавить путевой лист
        </div>
        <form id="add_list_form" method="post" action="/iwaterTest/backend.php">
            <select  class="classic" id="driver" name="driver" style="padding: 0px 0px 0px 5px;">
                <option value="All">Все водители</option>
                <?php echo select_driver(); ?>
            </select> <span style="border: 0; margin: 5px 10px 0px 10px; background-color: #eff3f6;">Дата</span>
            <input id="date_list" name="date" type="text" placeholder="Дата путевого (d/m/Y)">
            <input name="add_list" type="hidden">
            <input class="classic" name="submit" type="submit" value="Сформировать">
        </form>

    </div>
</div>

<script>
    $(document).ready(function() {
        $('.list_lists_from').datepicker({
            showOn: 'button',
            buttonText: 'Show date',
            buttonImageOnly: true,
            buttonImage: '/iwaterTest/css/image/calendar.png'
        });
        $('.list_lists_to').datepicker({
            showOn: 'button',
            buttonText: 'Show date',
            buttonImageOnly: true,
            buttonImage: '/iwaterTest/css/image/calendar.png'
        });
        $('#date_list').datepicker({
            showOn: 'button',
            buttonText: 'Show date',
            buttonImageOnly: true,
            buttonImage: '/iwaterTest/css/image/calendar.png'
        });

        drawTable(1);

        jQuery.validator.addMethod(
            "australianDate",
            function (value, element) {
                return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
            },
            "Введите дату в формате dd/mm/yyyy.");
        $("#add_list_form").validate({
            rules: {
                date: {australianDate: true}
            },
            errorPlacement: function(error, element) {
                error.insertAfter($("#add_list_form"));
            }
        });
    });

    function selectDate(ev) {
        $('.datepicker_icon').datepicker({}).datepicker('show');
    }

    // Управление периодом выгрузки
    function confirmOutputPeriod() {
        var startDate = $('.list_lists_from').val();
        var finishDate = $('.list_lists_to').val();
        var url = "/iwaterTest/admin/trav_list";

        if (startDate != "" && finishDate != "") {
            url += "?from=" + startDate + "&to=" + finishDate;
        } else if (startDate != "") {
            url += "?from=" + startDate;
        } else if (finishDate != "") {
            url += "?to=" + finishDate
        }

        window.location.href = url;
    }

    // Получение данных о путевых листах из базы, сборка таблицы и пагинатора
    function drawTable(page) {
        $.ajax({
            type: 'POST',
            url: '/iwaterTest/backend.php<?php if ($_GET['from'] && $_GET['to']) { echo '?from=' . $_GET['from'] . '&to=' . $_GET['to']; } else if ($_GET['from']) { echo '?from=' . $_GET['from']; } else if ($_GET['to']) { echo '?to=' . $_GET['to']; }  ?>',
            data: {
                list_lists: '',
                trav_page: page
            },
            success: function(xml) {
                var total_pages = $(xml).find('total').text();
                var current_page = $(xml).find('page').text();
                var selectCurrentPage = "";

                /** Собираем пагинатор */
                var start_page = (current_page - 3 < 1) ? 1 : current_page - 3;
                var finish_page = (current_page -1 + 4 > total_pages) ? total_pages : current_page - 1 + 4;
                var paginator_string = '';

                for (var i = start_page; i <= finish_page; i++) {
                    selectCurrentPage = i == current_page ? ' style="background-color: #74ccea; color: #fff;" ' : '';
                    paginator_string += '<input type="button" class="pagination" onclick="drawTable(' + i + ')" value="' + i + '" ' + selectCurrentPage + '>';
                }

                $('#list_lists_paginator').html(paginator_string); // Собрали постраничную навигацию

                var table_string = '';
                table_string += '<tr><th>Имя файла</th> <th>Карта</th> <th>Добавил</th> <th>Дата добавления</th> <th>Удалить</th></tr>';

                $(xml).find("row").each(function () {
                    var row = $(this).find('cell');

                    table_string += '<tr>';
                    table_string += '<td><a class="xlsx" onclick="downloadList(\'' + $(row[8]).text() + '\', \'' + $(row[6]).text() + '\', \'' + $(row[7]).text() + '\', \'' + $(row[5]).text() + '\', \'' + $(row[0]).text() + '\');" style="cursor: pointer; text-decoration: underline;">' + $(row[0]).text() + '</a></td>';
                    table_string += '<td><a class="map" href="/iwaterTest/map/' + $(row[8]).text() + '">Открыть</a></td>'
                    table_string += '<td>' + $(row[2]).text() + '</td>';
                    table_string += '<td>' + $(row[3]).text() + '</td>';
                    table_string += '<td><a class="delete" onclick="if (confirm(\' Подтвердить удаление?\')) { deleteList(' + $(row[5]).text() + '); } else { return false; }"><div class="delete_clients_div" type="text" role="textbox" style="background: url(../../css/image/delete.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; cursor: pointer; margin: 0 auto; "></div></a></td>';
                    table_string += '</tr>';
                });

                $('#list_lists_table').html(table_string);
            }
        });
    }

    $(function(){
        $('#driver option[value="' + 0 + '"]').text("Нераспределенные точки");
        $('#driver').val("All");
        $("#date, #date_list").datepicker();
        $("#date, #date_list").datepicker("option", "dateFormat", "dd/mm/yy");
    });

    // Метод удаления поставщика с оповещением о успешном завершении
    function deleteList(val) {
        $.ajax({
            type: "POST",
            url: "/iwaterTest/backend.php",
            data: {
                delete_list: val
            },
            success: function () {
                alert('Запись успешно удалена');
                window.location.reload();
            }
        });
    }

    function downloadList(date, driver_id, driver_n, list, file_name) {
        $('.loading').show();

        $.ajax({
            type: "POST",
            data: {
                createExcell: "",
                date: date,
                driver_id: driver_id,
                driver_n: driver_n,
                list: list,
                file_name: file_name
            },
            url: "/iwaterTest/backend.php",
            success: function (req) {
                $('.loading').hide();
                location.href = '/iwaterTest/files/' + req;
            }
        });
    }

</script>

<style media="screen">
    th {
        padding: 10px;
        color: gray;
    }
    .category_label {
        width: 100%;
        float: left;
        font-size: 21px;
        padding: 20px 0 20px 25px;
        border-bottom: 1px solid #e3e9f1;
    }
    .classic {
        width: 113px;
        padding: 5px 20px 5px 7px;
    }
    .list_lists {
        padding-left: 20px;
    }
    .list_lists_date {
        font-size: 17px;
        display: inline-flex;
    }
    .list_lists_date p {
        margin: 8px 5px 0px 11px;
        font-size: 15px;
    }
    .lists_filter {
        padding: 4px 20px;
        border-radius: 20px;
    }
    .ui-datepicker-trigger {
        position: relative;
        right: 207px;
        width: 25px;
        height: 25px;
        display: inline-block;
        margin: 4px 4px 0px 4px;
    }
    .pagination {
        width: auto;
        height: auto;
        background-color: #fff;
        border: 1px solid #e2e6ee;
        border-radius: 0px;
        color: gray;
        cursor: pointer;
        margin: 2px;
        padding: 9px 10px 20px 10px;
    }

    #add_list_form {
        width: 100%;
        float: left;
        display: inline-flex;
        margin-left: 20px;
    }
    #date_list {
        height: 20px;
        width: 170px;
        margin-left: 30px;
    }
    #driver {
        height: 25px;
        width: 160px;
    }
    #driver_list {
        display: flex;
        float: left;
        padding-left: 15px;
    }
    #list_lists_table {
        width: 1000px;
        min-height: 310px;
        text-align: center;
    }

    #list_lists_table tr:nth-child(1){
        background-color: #fff0;
    }

    #list_lists_paginator {
        float: right;
    }

    /* СТИЛИ ДАТА ПИКЕРА */
    .ui-widget-header {
        background-color: #fff;
    }

    .ui-datepicker th {
        padding: 0.7em 0em;
    }

    .ui-datepicker-calendar {
        background-color: #fff;
        border: 1px solid #e2e6ee;
        border-radius: 5px;
    }

    .ui-widget-header {
        border: 1px solid #e2e6ee;
        background: #fff;
    }

    #ui-datepicker-div span {
        padding: 0px;
        border: 0px;
    }

</style>
