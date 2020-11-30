<?php
   $access_level = check_perms('list_lists');
?>

<div class="main">
    <div class="main_form">
        <img src="../../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
<div id="lists" class="main_form">
    <div class="search">
        <div>Поиск путевого листа по дате</div>
        <form>
            <label for="from">Дата с</label>
            <input type="text" id="from" name="from">
            <label for="to">По</label>
            <input type="text" id="to" name="to">
            <input type="button" class="classic" value="Фильтр" placeholder="Дата путевого (d/m/Y)" onclick="update_table();update_pagination();">
            <input name="list_lists_upd" type="hidden">

        </form>
    </div>
    <div class="title">Сформированные путевые листы</div>
    <div class="table">
        <table id="list_lists" class="main_table">

        </table>
       <div id="pagination">
           <?php
           $lists_in_page = 10;
           $iCurr = (empty($_GET['page']) ? 1 : intval($_GET['page']));
           ?>
       </div>
    </div>
</div>
        </div>
    </div>

<script>
    $(function(){
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
        } );
        update_table();
        update_pagination()
    });

    function update_table() {
        var data = [];
        data[0] = $('#from').val();
        data[1] = $('#to').val();
        data = JSON.stringify(data);
        $.ajax({
            type: "POST",
            data: {
                list_lists: data,
                current_page: <?php echo $iCurr ?>,
                lists_in_page: <?php echo $lists_in_page?>
            },
            url: "/iwaterTest/backend.php",
            success: function (req) {
                create_table(req);
            }
        });

    }
    function update_pagination(){
        var data = [];
        data[0] = $('#from').val();
        data[1] = $('#to').val();
        data = JSON.stringify(data);
        $.ajax({
            type: "POST",
            data: {
                page_lists: data,
                current_page: <?php echo $iCurr ?>,
                lists_in_page: <?php echo $lists_in_page?>
            },
            url: "/iwaterTest/backend.php",
            success: function (req) {
                create_pagination(req);
            }
        });
    }

    $(document).ready(function () {
        jQuery.validator.addMethod(
            "australianDate",
            function (value, element) {
                // put your own logic here, this is just a (crappy) example
                return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
            },
            "Введите дату в формате dd/mm/yyyy.");
        $("#add_list_form").validate({
            rules: {
                date: {australianDate: true}
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            }
        });
    });

    function create_table(req) {
        $('#list_lists tr').each(function () {
            $(this).parent().remove()
        });
        var row = document.getElementById('list_lists').insertRow(-1);
        var cell = row.insertCell(-1).innerHTML = "Имя файла";
        var cell = row.insertCell(-1).innerHTML = "Карта";
        var cell = row.insertCell(-1).innerHTML = "Добавил";
        var cell = row.insertCell(-1).innerHTML = "Дата добавления";
        row.insertCell(-1).innerHTML =  "";

        console.log(req);

        var i = 0;
        var rows = req.getElementsByTagName('rows')[0];
        l = rows.getElementsByTagName('row').length;
        while (i < l) {
            var request = rows.getElementsByTagName('row')[i];

            console.log(request.getElementsByTagName("cell")[3].innerHTML);

            var file = request.getElementsByTagName("cell")[0];
            var name = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
            var create_date = request.getElementsByTagName("cell")[3].innerHTML;
            var map_num = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
            var driver_id = request.getElementsByTagName("cell")[6].childNodes[0].nodeValue;
            var driver_name = request.getElementsByTagName("cell")[7].childNodes[0].nodeValue;
            var date = request.getElementsByTagName("cell")[8].childNodes[0].nodeValue;
            var list = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;

            row = document.getElementById('list_lists').insertRow(-1);
            cell = row.insertCell(-1).innerHTML = '<a class="xlsx" href="/iwaterTest/files/' + file.innerHTML + '">' + file.innerHTML + '</a>';
            row.insertCell(-1).innerHTML = '<a href="/iwaterTest/map/'+map_num+'" > Карта</a>';
            row.insertCell(-1).innerHTML = name;
            row.insertCell(-1).innerHTML = create_date;
            row.insertCell(-1).innerHTML = '<a href="#" class="delete_list">Удалить</a>';
            row.insertCell(-1).innerHTML = '<div id="driver_id">'+driver_id+'</div>';
            $(row.lastChild).hide();
            row.insertCell(-1).innerHTML = '<div id="driver_name">'+driver_name+'</div>';
            $(row.lastChild).hide();
            row.insertCell(-1).innerHTML = '<div id="date">'+date+'</div>';
            $(row.lastChild).hide();
            row.insertCell(-1).innerHTML = '<div id="list">'+list+'</div>';
            $(row.lastChild).hide();
            i++;
        }
        update_xlsx();
        update_delete_links();

    }
    function create_pagination(req){
        $("#pagination")[0].innerHTML = req;
    }
    function update_delete_links(){
        $(".delete_list").click(function(){
            var file = $(this).parent().parent().children().eq(0).children().text() // текст в соседней ячейке
            $.ajax({
                type: "POST",
                data: {
                    delete_list: file
                },
                url: "/iwaterTest/backend.php",
                success: function (req) {
                    alert("Файл удалён");
                    location.reload()

                }
            });
        });
    }

    function update_xlsx () {
            $(".xlsx").click(function (event) {
                event.preventDefault();
                var tds = $(this).parent().parent().children("td");
                var driver_id = tds.children("#driver_id")[0].innerText;
                var driver_n = tds.children("#driver_name")[0].innerText;
                var date = tds.children("#date")[0].innerText;
                var list = tds.children("#list")[0].innerText;
                var file_name = tds[0].innerText;
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
//                        window.downloadFile('/iwaterTest/files/' + req);
                    }
                });
            });
        };


</script>
