<div class="main">
    <?php
    $date = trim(filter_input(INPUT_POST, 'date', FILTER_SANITIZE_SPECIAL_CHARS));
    $list_id = trim(filter_input(INPUT_POST, 'list_id', FILTER_SANITIZE_SPECIAL_CHARS));
    $drivers = trim(filter_input(INPUT_POST, 'count', FILTER_SANITIZE_SPECIAL_CHARS));
    $drivers = intval($drivers);
    ?>
    <div class="category_label">
      Путевые листы
      <hr color="#e3e9f1" size="1px" width="100%">
    </div>
    <img src="/iwaterTest/css/image/loading-gallery.gif" class="loading" alt="" style="width: 150px; display: none; top: 50%, left: 50%;"/>
    <div style="display: inline-block ">Общий путевой лист:</div>
    <div style="display: inline-block">
        <a target="_blank" class="xlsx" onclick="downloadList(<?php echo $date; ?>, '', '', '', '')" style="cursor: pointer;">
            <div style="display: none">
                <div id="date"><?php echo $date ?></div>
                <div id="driver_id"></div>
                <div id="driver_n"></div>
            </div>
            XLSX</a>
        <a href="/iwaterTest/map/<?php echo $date; ?>"> Карта</a></div>
    <br>
    <?php
    for ($i = 0; $i < $drivers; $i++) {
        ?>
        <br>
        <div style="display: inline-block">Путевой лист водителя: <?php echo $_POST['driver_name_' . $i] ?> </div>
        <div style="display: inline-block">
            <a class="xlsx" target="_blank" style="cursor: pointer; text-decoration: underline;" onclick="downloadList(<?php echo "'$date', '" . $_POST['driver_id_' . $i] . "', '" . $_POST['driver_name_' . $i] . "', '', ''";?>)">
                <div style="display: none">
                    <div id="date"><?php echo $date ?></div>
                    <div id="driver_id"><?php echo $_POST['driver_id_' . $i] ?></div>
                    <div id="driver_name"><?php echo $_POST['driver_name_' . $i] ?></div>
                </div>
                XLSX</a>
            <a href="/iwaterTest/map/<?php echo $date; ?>?driver_id=<?php echo $_POST['driver_id_' . $i] ?>"> Карта</a>
            <a target="_blank" class="blank_xlsx" href="/iwaterTest/files/blank_<?php echo date('j.m.Y', $date) .'(driver)'. $_POST['driver_name_' . $i] . '.xlsx' ?>"> Бланк</a>
        </div>
    <?php } ?>
</div>
</div>
<script>
   /**
    * Стартовые операции
   */
   $(function() {
      $(".blank_xlsx").click(function(event) {
         event.preventDefault();
         var date = $(this).parent().find(".xlsx").children("div").children("#date").text();
         var driver_id = $(this).parent().find(".xlsx").children("div").children("#driver_id").text();
         var driver_n = $(this).parent().find(".xlsx").children("div").children("#driver_name").text();
         $('.loading').show();
         $.ajax({
            type: "POST",
            data: {
               createExcellMotivation: "",
               date: date,
               driver_id: driver_id,
               driver_n: driver_n

            },
            url: "/iwaterTest/backend.php",
            success: function(req) {
               $('.loading').hide();
               window.downloadFile('/iwaterTest/files/' + req);
            }
         });
      });
   });

   /**
    * Генерация и выгрузка путевого листа в Excel
   */
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
         success: function(req) {
            $('.loading').hide();
            location.href = '/iwaterTest/files/' + req;
         }
      });
   }
</script>

<style media="screen">
.category_label {
   width: 100%;
   display: contents;
   float: left;
   font-size: 21px;
   padding: 20px 0 20px 25px;
}
</style>
