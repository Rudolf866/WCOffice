<div class="main">
    <div class="delete_list_table">
        <form id="delete_list_table" method="post" action="/iwaterTest/backend.php">
            <table id="list_clients" class="main_table">

            </table>
<!--            <div id="pagination">-->
<!--                --><?php
//                $lists_in_page = 10;
//                $iCurr = (empty($_GET['page']) ? 1 : intval($_GET['page']));
//                ?>
<!--            </div>-->
            <input name="delete_list_table" type="hidden">
        </form>
    </div>
</div>
<script>
    function update_table() {
        //console.log(data);
        $.ajax({
            type: "POST",
            data: {
                delete_list_clients: " "
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
        var cell = row.insertCell(-1).innerHTML = "";
        cell = row.insertCell(-1).innerHTML = "";
        cell = row.insertCell(-1).innerHTML = "";
        cell = row.insertCell(-1).innerHTML = '<div style="min-width:100px">Название</div>';
        cell = row.insertCell(-1).innerHTML = "id клиента";
        cell = row.insertCell(-1).innerHTML = "Удалил";
        cell = row.insertCell(-1).innerHTML = "Время удаления";
        cell = row.insertCell(-1).innerHTML = '<div style="width:116px">Регион</div>';
        cell = row.insertCell(-1).innerHTML = '<div style="min-width:200px">Адрес</div>';
        cell = row.insertCell(-1).innerHTML = "Контакт";

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
            var user_id = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;
            var time = request.getElementsByTagName("cell")[6].childNodes[0].nodeValue;
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
            cell = row.insertCell(-1).innerHTML = '<a title="Восстановить" class="restablish_clients" href="#" id="restablish_'+client_id+'"><div id="restablish_clients" type="text" role="textbox" class="" style="    background: url(../../css/image/restablish.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; "></div></a>';
            cell = row.insertCell(-1).innerHTML = '<a title="Список заказов" href="/iwaterTest/admin/list_orders?client_order='+client_id+'"><div id="client_orders" type="text" role="textbox" class="" style="    background: url(../../css/image/lists.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; "></div></a>';
            cell = row.insertCell(-1).innerHTML = '<a title="Удалить клиента" class="delete_clients" href="#?'+client_id+'" id="delete_'+client_id+'"><div class="delete_clients_div" type="text" role="textbox" " style="    background: url(../../css/image/delete.png) 0 0 no-repeat;background-size: contain; width:16px; height: 16px; "></div></a>';
            cell = row.insertCell(-1).innerHTML = name;
            cell = row.insertCell(-1).innerHTML = client_id ;
            cell = row.insertCell(-1).innerHTML = user_id ;
            cell = row.insertCell(-1).innerHTML = time ;
            cell = row.insertCell(-1).innerHTML = region.join(';<br>');
            cell = row.insertCell(-1).innerHTML = address.join(';<br>');
            cell = row.insertCell(-1).innerHTML = contact.join(';<br>');
            i++;
        }
        update_links();

    }

    $(document).ready(function() {
        update_table();
    });
    function update_links(){
        $(".delete_clients").click(function(){
            var result = confirm("Вы действительно хотите удалить клиента?");
            if(result == true){
                var client_id = this.id.split("_");
                client_id = client_id[1];
                $.ajax({
                    type: "POST",
                    data: {
                        destroy_client: client_id
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (req) {
                        update_table();
                    }
                });
            }
        });
        $(".restablish_clients").click(function(){
            var result = confirm("Вы действительно хотите восстановить клиента?");
            if(result == true){
                var client_id = this.id.split("_");
                client_id = client_id[1];
                $.ajax({
                    type: "POST",
                    data: {
                        restablish_client: client_id
                    },
                    url: "/iwaterTest/backend.php",
                    success: function (req) {
                        update_table();
                    }
                });
            }
        });
    }
</script>
