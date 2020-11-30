<div class="main">
    <table class="edit_list">
      <caption>Путевой лист</caption>
    </table>
    <img src="/iwaterTest/css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
    <input type="submit" value="Выгрузить" style="float: left;" onclick="update_xlsx();">
    <div>
<script>
    var order_list; // Массив содержащий список заказов для указанного водителя
    var out_order_list = []; // Массив хронящий массивы "нарезанных" путевых листов
    var date = '1535317200';

    $.ajax({
        type: "POST",
        data: {
            cut_list: '',
            driver: '<?php echo $_GET['driver'] ?>',
            date: date
        },
        url: "/iwaterTest/backend.php",
        success: function(res) {
            order_list = JSON.parse(res);
            var out = $(".edit_list").html();

            for (var i = 0; i < order_list.length; i++) {
                out += '<tr class="border-bottom" onclick="cutListMethod(this);"><td>' + (i + 1) + "</td><td>" + order_list[i]['id'] + "</td><td>" + order_list[i]['address'] + "</td></tr>";
            }

            $(".edit_list").html(out);
        }
    });

    function cutListMethod(id) {
      $(id).css("border-bottom", "solid 2px");

      console.log([0, id.childNodes[0].innerHTML]);

      out_order_list.push([0, id.childNodes[0].innerHTML]);
      out_order_list.push([(id.childNodes[0].innerHTML - 1 + 2), order_list.length]);

      console.log(out_order_list);
   }

   function update_xlsx () {
       $('.loading').show();
      for (var i = 0; i < out_order_list.length; i++) {
         $.ajax({
             type: "POST",
             data: {
                 createExcell: "",
                 date: date,
                 driver_id: <?php echo $_GET['driver']; ?>,
                 driver_n: "Водитель",
                 start: out_order_list[i][0],
                 finish: out_order_list[i][1]
             },
             url: "/iwaterTest/backend.php",
             success: function (req) {

                var link = document.createElement('a');
                 link.setAttribute('href', '/iwaterTest/files/' + req);
                 link.setAttribute('download', req);
                 link.click();
             }
         });
      }
      $('.loading').hide();
   }
</script>

<style>
.edit_list {
   border-collapse: collapse;
   float: left;
   margin: auto;
   border: solid 2px;
}

.edit_list td {
   padding: 8px;
}

.edit_list tr:hover{
   border-bottom: solid 2px;
}

</style>
