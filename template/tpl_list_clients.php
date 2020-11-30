<?php
   $access_level = check_perms('list_clients');
?>

<div class="main">
    <div class="clients_list_table">
        <form id="list_edit_client" method="post" action="/iwaterTest/backend.php">
            <div class="name_title">
                <div class="name_position">Список клиентов</div>
                <div class="add_position"><a href="http://iwatercrm.ru/iwaterTest/admin/add_client/"><span class="img_add"></span>Добавить клиента</a></div>
            </div>
            <div class="search_row">
            <?php if (check_perms('edit_clients')) { ?>

<!--                <input name="submit" id="save_change" type="submit" value="Сохранить изменения">-->
            <?php } ?>
            <input class="search_data" id="client_num" name="client_num" type="text" placeholder="№ клиента" value="<?php if(isset($_GET['num']))echo $_GET['num'] ?>">
            <input class="search_data" id="client_name" name="client_name" type="text" placeholder="Название клиента" value="<?php if(isset($_GET['name']))echo $_GET['name'] ?>">
            <input class="search_data" id="client_phone" name="client_phone" type="text" placeholder="Телефон" value="<?php if(isset($_GET['cont']))echo $_GET['cont'] ?>">
            <input class="search_button" type="button" value="Поиск" onclick="update_table()">
            <input class="reset_button" type="button" value="Сброс" onclick="refreshFilter()">

            </div>
            <table id="list_clients" class="main_table">

            </table>
            <div id="pagination">
                <?php
                $lists_in_page = 10;
                $iCurr = (empty($_GET['page']) ? 1 : intval($_GET['page']));
                ?>
            </div>
            <input name="list_clients_upd" type="hidden">
        </form>
    </div>
</div>

                <script>
                    function setFilter(){
                        update_table();
                        update_pagination();
                    }
                    function refreshFilter(){
                        $('#client_num').val("");
                        $('#client_name').val("");
                        $('#client_phone').val("");
                        update_table();
                        update_pagination();
                    }
                    update_table();

                    function update_table() {
                        var data = [];
                        data[0] = $('#client_num').val();
                        data[1] = $('#client_name').val();
                        //data[2] = $('#client_phone').val();
			            data[2] = $('#client_phone').val().replace(/[^\d]/g, '');
                        data = JSON.stringify(data);
                        //console.log(data);
                        $.ajax({
                            type: "POST",
                            data: {
                                lists_in_page: data,
                                current_page: <?php echo $iCurr ?>,
                                lists_in_page: <?php echo $lists_in_page?>
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (req) {
                                create_table(req);
                            }
                        });

                    }

                    function create_table(req) {
                        $('#list_clients tr').each(function () {
                            $(this).parent().remove()
                        });
                        var row = document.getElementById('list_clients').insertRow(-1);

//                        cell = row.insertCell(-1).innerHTML = "Тип";
                        cell = row.insertCell(-1).innerHTML = '<div style="min-width:65px" align="center">Тип</div>';
                        cell = row.insertCell(-1).innerHTML = '<div style="min-width:100px" align="center">Название</div>';
                        cell = row.insertCell(-1).innerHTML = '<div style="min-width:65px" align="center">id клиента</div>';
//                        cell = row.insertCell(-1).innerHTML = "id клиента";
                        cell = row.insertCell(-1).innerHTML = '<div style="width:116px" align="center">Регион</div>';
                        cell = row.insertCell(-1).innerHTML = '<div style="min-width:200px" align="center">Адрес</div>';
                        cell = row.insertCell(-1).innerHTML = '<div align="center">Дополинительная информация</div>';
//                        cell = row.insertCell(-1).innerHTML = "Контакт";
                        var cell = row.insertCell(-1).innerHTML = "";
                        cell = row.insertCell(-1).innerHTML = '<div style="min-width:65px" align="center">Управление</div>';
                        cell = row.insertCell(-1).innerHTML = "";

                        var i = 0;
                        var rows = req.getElementsByTagName('rows')[0];
                        l = rows.getElementsByTagName('row').length;
                        var last_id;
                        while (i < l) {
                            var request = rows.getElementsByTagName('row')[i];
                            var id = request.getAttribute('id');
                            var db_id = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
                            var type = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
                            var name = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
                            var client_id = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
                            if(client_id == last_id){
                                i++;
                                continue;
                            }
                            last_id = client_id;
                            var array_addr = $(request).find(".addr");
                            var region = [], address = [], contact = [], coords = [];
                            for(var j=0;j<array_addr.length;j++){
                                region[j] = array_addr.eq(j).children().eq(0).text();
                                address[j] = array_addr.eq(j).children().eq(1).text();
                                contact[j] = array_addr.eq(j).children().eq(2).text();
                                coords[j] = array_addr.eq(j).children().eq(3).text();
                            }

                            row = document.getElementById('list_clients').insertRow(-1);


//                            cell = row.insertCell(-1).innerHTML = '<select name="type[]"><option value="0"' + ((type == 0) ? ' selected' : '') + '>Физ. лицо</option><option value="1"' + ((type == 1) ? 'selected' : '') + '>Юр. лицо</option></select>';
                            // Ячейка должна быть нередактируемой
                            cell = row.insertCell(-1).innerHTML = (type == 0) ? "Физ. лицо" : "Юр. лицо";
                            cell = row.insertCell(-1).innerHTML = name;
//
//                            cell = row.insertCell(-1).innerHTML = '<input class="id_readonly" name="client_id[]" type="text" value="' + client_id + '" readonly>';
                            cell = row.insertCell(-1).innerHTML = client_id;
                            cell = row.insertCell(-1).innerHTML = region.join(';<br>');
                            cell = row.insertCell(-1).innerHTML = address.join(';<br>');
                            cell = row.insertCell(-1).innerHTML = contact.join(';<br>');
                            i++;
                            cell = row.insertCell(-1).innerHTML = '<?php if ($access_level > 1) echo "<a title=\"Редактировать\" href=\"/iwaterTest/admin/edit_clients?id='+db_id+'\"><div id=\"edit_order\" type=\"text\" role=\"textbox\" class=\"edit_clients\" style=\"background: url(../../css/image/edit.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; margin: 0 auto; \"></div></a>"; ?>';
                            cell = row.insertCell(-1).innerHTML = '<a title="Список заказов" href="/iwaterTest/admin/list_orders?client_order='+client_id+'"><div id="client_orders" type="text" role="textbox" class="order_list" style="    background: url(../../css/image/lists.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; margin: 0 auto; "></div></a>';
                            cell = row.insertCell(-1).innerHTML = '<?php if ($access_level > 1) echo "<a title=\"Удалить клиента\" class=\"delete_clients\" onclick=\"if (confirm(\' Подтвердить удаление?\')) { deleteList(' + client_id + '); } else { return false; }\"><div class=\"delete_clients_div\" type=\"text\" role=\"textbox\" style=\"background: url(../../css/image/delete.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; margin: 0 auto; \"></div></a>"; ?>';

                        }
                        update_links();

                    }

//                    document.onkeyup = function (e) {
//                        e = e || window.event;
//                        if (event.which == '13') {
//                           update_table();
//
//                        }
//                    }
//                    document.onkeydown = function (e) {
//                        if((event.which = '17') && (event.which == '70'))
//                        {
//                            alert("!!!!");
//                            $("#list_edit_client")[0].submit.click()
//                        }
//                    };

                    $(document).ready(function() {
                        update_pagination();
                        $("#list_edit_client").keydown(function(event){
                            if(event.keyCode == 13) {
                                event.preventDefault();
                                return false;
                            }
                        });
                    });
    function deleteList(val) {
      console.log(val);
      $.ajax({
         type: "POST",
         url: "/iwaterTest/backend.php",
         data: {
            delete_client: val
         },
         success: function () {
            alert('Запись успешно удалена');
            window.location.reload();
         }
      });
   }

                    function update_links(){
                       /* $(".delete_clients").click(function(){
                            var result = confirm("Вы действительно хотите удалить клиента?");
                            if(result == true){
                                var client_id = this.id.split("_");
                                client_id = client_id[1];
                                $.ajax({
                                    type: "POST",
                                    data: {
                                        delete_client: client_id
                                    },
                                    url: "/iwaterTest/backend.php",
                                    success: function (req) {
                                        update_table();
                                        update_pagination();
                                    }
                                });
                            }
                        });*/
                    }
                    function update_pagination(){
                        var data = [];
                        data[0] = $('#client_num').val();
                        data[1] = $('#client_name').val();
                        data[2] = $('#client_phone').val();
                        data = JSON.stringify(data);
                        $.ajax({
                            type: "POST",
                            data: {
                                page_clients: data,
                                current_page: <?php echo $iCurr ?>,
                                lists_in_page: <?php echo $lists_in_page?>
                            },
                            url: "/iwaterTest/backend.php",
                            success: function (req) {
                                create_pagination(req);
                            }
                        });
                    }
                    function create_pagination(req){
                        $("#pagination")[0].innerHTML = req;
                    }


                </script>

                </script>
