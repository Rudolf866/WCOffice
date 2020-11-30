<?php
$settings = get_settings();
$e_mail = $settings[0];
$e_mail = $e_mail['data'];
$e_mail = json_decode($e_mail);
$e_mail_value = "";
for ($i=0;$i<count($e_mail);$i++){
    $e_mail_value .= $e_mail[$i];
    if($i != count($e_mail)-1) $e_mail_value .= ",";
}
?>

<div class="main">
    <div class="main_form">
      <table id="all_settings" border="1">
         <tr>
            <td>
              <form id="settings" method="post" action="/iwaterTest/backend.php">
                  <label>Один или несколько e_mail, куда будут отправляться оповещения. Через запятую. </label>
                  <input id="e_mail" name="e_mail" type="text" placeholder="e_mail" value="<?php echo $e_mail_value ?>">
                  <br>
                  <input name="settings" type="hidden">
                  <input class="classic" name="submit" type="submit" value="Сохранить">
              </form>
           </td>
           <td style="width: 300px;">
             <div class="period_list">
                 <div id="editor_period_0" class="0">
                     <input name="period" input="text" id="period" placeholder="Название">
                     <input name="submit" type="submit" id="up_period" value="↑" onclick="swapUp(this.parentElement);">
                     <input name="submit" type="submit" id="down_period" value="↓" onclick="swapDown(this.parentElement);">
                     <input name="submit" type="submit" id="delete_period" value="x" onclick="destroyPeriod(this.parentElement);">
                 </div>
             </div>
             <input name="input_timing" type="hidden">
             <input name="submit" type="submit" id="send_period" value="Сохранить">
             <input name="submit" type="submit" id="add_period" value="Добавить">
           </td>
        </tr>
     </table>
    </div>

</div>

<script>
    var last_id = 0;
    var period;

    // Первичная выгрузка данных
    $.ajax({
        type: 'POST',
        data: {
            select_period: ""
        },
        response: "json",
        url: '/iwaterTest/backend.php',
        success: function(req) {
            console.log(req);

            var list = JSON.parse(req);
            period = list['period'];
            period = JSON.parse(period);

            for (var i = 0; i < period.length; i++) {
                if (i == 0) {
                    $('#editor_period_0').children('#period').val(period[i]['unit']);
                } else {
                    $("#editor_period_0").clone(true).attr('id', 'editor_period_' + i).appendTo(".period_list").find('input:text').val('');
                    $('#editor_period_' + i).attr('class', i);
                    $('#editor_period_' + i).children('#period').val(period[i]['unit']);
                }
            }

            last_id = period.length;
        }
    });

    // Поднять текущий элемент
    function swapUp(e) {
       var temp_period;
       console.log(e.className);

       temp_period = $('#editor_period_' + e.className).children('#period').val();

       if (e.className > 0) {
          $('#editor_period_' + e.className).children('#period').val($('#editor_period_' + (e.className - 1)).children('#period').val());

          $('#editor_period_' + (e.className - 1)).children('#period').val(temp_period);
       }
    }

    // Опустить элемент
    function swapDown(e) {
      var temp_period;
      console.log(e.className);
      console.log(e.className - 1 + 2);

      temp_period = $('#editor_period_' + (e.className - 1 + 2)).children('#period').val();

      if (e.className < last_id) {
         $('#editor_period_' + (e.className - 1 + 2)).children('#period').val($('#editor_period_' + e.className).children('#period').val());

         $('#editor_period_' + e.className).children('#period').val(temp_period);
      }
    }

    // Удалить период
    function destroyPeriod(e) {
        for (var number = e.className; number < last_id; number++) {
            $('#editor_period_' + (number - 1)).children('#period').val($('#editor_period_' + number).children('#period').val());
        }

        last_id--;
        $('#editor_period_' + (last_id)).detach();
    }

    // Отправка координат
    $("#send_period").click(function() {
        var period = new Array();
        for (var i = 0; i < last_id; i++) {
            var one = $('#editor_period_' + i).children('#period').val();
            one = { unit: one};

            period.push(one);
        }
        var finish = { "period": period };
        console.log(JSON.stringify(finish));

        $.ajax({
            type: 'POST',
            data: {
                change_period: JSON.stringify(period),
            },
            url: '/iwaterTest/backend.php',
            success: function () {
                alert('Обновлено!');
            	console.log('sended');
            }
        });
    });

    // Добавить период
    $("#add_period").click(function() {
        $("#editor_period_0").clone(true).attr('id', 'editor_period_' + (last_id + 1)).appendTo(".period_list").find('input:text').val('');
        $("#editor_period_" + (last_id + 1)).attr('class', last_id + 1);
        last_id++;
    });
</script>
