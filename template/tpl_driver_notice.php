
<?php
$session = get_session();

?>
<div class="main">
    <div class="main_form">
        <img src="../../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
        <div>
            Дата: <?php echo $date = date("d/m/Y") ?>
        </div>
        <br>
        <form id="driver_list" method="post" action="/iwaterTest/backend_driver.php">
            <?php if($session['role'] != 3) { ?>
                <select  class="classic" id="driver" name="driver">
                    <?php echo select_driver(); ?>
                </select>
            <?php }else{ ?>
                <input type="hidden" id="driver" name="driver" value="<?php echo $session['id']  ?>">
            <?php } ?>
            <input type="hidden" name="driver_list">
            <input class="classic" id="sumbit_driver_notice" ONCLICK="show_notice()" name="submit" type="button" value="Отобразить">
            <br>
        </form>
    </div>
    <h3>
        Уведомления
    </h3>

    <div class="mail-div">

        </div>
</div>

<?php if($session['role'] != 3) { ?>
    <script>
        $( function() {
            $("#sumbit_driver_notice").show();
        });

    </script>
<?php }else{ ?>
    <script>
        $( function() {
            $("#sumbit_driver_notice").hide();
            $("#sumbit_driver_notice").click();
        });

    </script>
<?php } ?>

<script>
    function toggle_notice(el){
        $(el).find("div").toggle();
        if($(el).hasClass("m-new")){
            $.ajax({
                type: "POST",
                data: {
                    driver_notice_to_read: "",
                    notice: el.id
                },
                url: "/iwaterTest/backend_driver.php",
                success: function (req) {
                    $(".loading").hide();
                    $(el).removeClass("m-new")
                }
            });
        }
    }
    function show_notice(){
        $(".loading").show();
        $.ajax({
            type: "POST",
            data: {
                driver_notice: "",
                driver: $("#driver").val()
            },
            url: "/iwaterTest/backend_driver.php",
            success: function (req) {
                $(".loading").hide();
                update_notice(req);
            }
        });
    }

    function update_notice(req){
        $(".mail-div").empty();
        $(".mail-div").innerHTML = "";
        var i = 0;
        var rows = req.getElementsByTagName('rows')[0];
        l = rows.getElementsByTagName('row').length;
        while (i < l) {
            var request = rows.getElementsByTagName('row')[i];
            var date = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
            var title = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
            var message = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
            var noticed = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
            var read = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
            var id = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;

            var html = $(".mail-div").innerHTML;
            var html_notice = "";
            if(read == 0){
                html_notice = '<a id="notice_'+id+'" onclick="toggle_notice(this)" class="mail-p m-new">' +
                    '<time>'+date+'</time>' +
                    '<b>'+title+'</b> <i class="fa fa-check"></i>' +
                    '  <div><hr><b>'+message+'</b></div>' +
                    '</a>';
            }else{
                html_notice = ' <a onclick="toggle_notice(this)" class="mail-p">' +
                    '<time>'+date+'</time>' +
                    '<b>'+title+'</b> <i class="fa fa-check"></i>' +
                    '  <div><hr><b>'+message+'</b></div>' +
                    '</a>';
            }

            $(".mail-div").append(html_notice);


            i++;
        }
        if(rows.getElementsByTagName('row').length ==0){
            $(".mail-div").append('<div><h1>Уведомлений нет</h1></div>');
        }

    }
</script>
