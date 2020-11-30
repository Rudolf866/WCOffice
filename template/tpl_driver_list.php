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
            <input class="classic" id="submit_driver_list" ONCLICK="update_driver_list()" name="submit" type="button" value="Отобразить">
            <br>
        </form>
    </div>
    <div id="">
        <h3>
            Путевой лист
        </h3>
        <table id="driver_list_table" class="main_table">

        </table>
    </div>


    <div id="modal_form"><!-- Сaмo oкнo -->
        <span id="modal_close">X</span> <!-- Кнoпкa зaкрыть -->
        <div class="title_date">Подтверждение выполнения заказа</div>
        <form id="driver_dome_form" method="post" action="/iwaterTest/backend_driver.php">
            <input id="order_id" name="id" type="hidden">
            <p><label for="tank">Количество сданных бутылок* </label><input name="tank" type="number"
                                                                            id="tank"></p>
            <p><label for="comment">Комментарий водителя </label><input name="comment" type="text"
                                                                        id="comment"></p>
            <div style="display: none" class="error"> "Количество сданных бутылок" - обязательное для заполнение поле, принимаются ТОЛЬКО целочисленные значения от 0 до 1000 </div>
            <div id="datepicker_submit">
                <input id="submit" name="submit" type="button" value="Сохранить" onclick="driver_done_save()">
            </div>
        </form>

    </div>
    <div id="modal_form2"><!-- Сaмo oкнo -->
        <span id="modal_close2">X</span> <!-- Кнoпкa зaкрыть -->
        <div class="title_date">Дата новой доставки</div>
        <form id="driver_cancel_form" method="post" action="/iwaterTest/backend_driver.php">
            <input id="order_id2" name="id" type="hidden">
            <p class=""><label for="group1">Причина: </label><br>
                <input type="radio" name="group1" value="Сломался">Сломался<br>
                <input type="radio" name="group1" value="Не успел">Не успел<br>
                <input type="radio" name="group1" value="Отменил клиент">Отменил клиент<br>
                <input type="radio" name="group1" value="Неверный адрес">Неверный адрес<br>
                <input type="radio" name="group1" value="Другое">Другое<br>
                <textarea style="display: none" placeholder="Напишите причину"></textarea>

                <!--                    <p class="datepicker_p"><label for="time">Время: </label><input id="time" name="time" type="text">-->
            </p>

            <p>
                <label for="group2">Согласовано с диспетчером: </label>
                <input type="radio" name="group2" value="0">Нет
                <input type="radio" name="group2" value="1">Да<br>
            </p>
            <div style="display: none" class="error"> Необходимо заполнить все поля </div>

            <div id="datepicker_submit">
                <input id="cancel_button" name="submit" type="button" value="Сохранить" onclick="driver_cancel_save()">
            </div>
        </form>

    </div>
    <div id="overlay"></div><!-- Пoдлoжкa -->
    <div id="overlay2"></div><!-- Пoдлoжкa -->
    <?php if($session['role'] != 3) { ?>
        <script>
            $( function() {
                $("#submit_driver_list").show();
            });

        </script>
    <?php }else{ ?>
        <script>
            $( function() {
                $("#submit_driver_list").hide();
                $("#submit_driver_list").click();
            });

        </script>
    <?php } ?>

</div>
<script>
    $(function(){

        $("#driver_cancel_form input[name='group1']").click(function(){
            var val = $("#driver_cancel_form input[name='group1']:checked").val();
           if(val == "Другое"){
               $("#driver_cancel_form textarea").show();
           }else{
               $("#driver_cancel_form textarea").hide();
           }
        });
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
        $('a#modal_link2').click(function (event) { // лoвим клик пo ссылки с id="go"
            event.preventDefault(); // выключaем стaндaртную рoль элементa
            $('#overlay2').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
                function () { // пoсле выпoлнения предъидущей aнимaции
                    $('#modal_form2')
                        .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                        .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
                });
        });
        /!* Зaкрытие мoдaльнoгo oкнa, тут делaем тo же сaмoе нo в oбрaтнoм пoрядке *!/
        $('#modal_close2, #overlay2').click(function () { // лoвим клик пo крестику или пoдлoжке
            $('#modal_form2')
                .animate({opacity: 0, top: '45%'}, 200,  // плaвнo меняем прoзрaчнoсть нa 0 и oднoвременнo двигaем oкнo вверх
                    function () { // пoсле aнимaции
                        $(this).css('display', 'none'); // делaем ему display: none;
                        $('#overlay2').fadeOut(400); // скрывaем пoдлoжку
                    }
                );
        });
    });
    function update_driver_list(){
        $(".loading").show();
        $.ajax({
            type: "POST",
            data: {
                driver_list: "",
                driver: $("#driver").val(),
                date: "<?php echo date("d/m/Y") ?>"
            },
            url: "/iwaterTest/backend_driver.php",
            success: function (req) {
                $(".loading").hide();
                update_table(req);
            }
        });
    }
    function update_table(req) {
        $('#driver_list_table tr').each(function () {
            $(this).parent().remove()
        });
        if(req.getElementsByTagName('row').length == 0){
            $("#driver_list_table").append('<div><h1>На этот день заказов нет</h1></div>');
            return 0;
        }
        var row = document.getElementById('driver_list_table').insertRow(-1);
        var cell = row.insertCell(-1).innerHTML = "";
        var cell = row.insertCell(-1).innerHTML = "";
        var cell = row.insertCell(-1).innerHTML = "Порядок заезда";
        var cell = row.insertCell(-1).innerHTML = "Номер клиента";
        var cell = row.insertCell(-1).innerHTML = "Имя ";
        var cell = row.insertCell(-1).innerHTML = "Адрес";
        var cell = row.insertCell(-1).innerHTML = "Контакты";
        var cell = row.insertCell(-1).innerHTML = "Время";
        var cell = row.insertCell(-1).innerHTML = "Ag";
        var cell = row.insertCell(-1).innerHTML = "Dp";
        var cell = row.insertCell(-1).innerHTML = "Ё";
        var cell = row.insertCell(-1).innerHTML = "Pl";
        var cell = row.insertCell(-1).innerHTML = "Oth";
        var cell = row.insertCell(-1).innerHTML = "Тара";
        var cell = row.insertCell(-1).innerHTML = "Оборудование";
//        row.insertCell(-1).innerHTML = "";

        var i = 0;
        var rows = req.getElementsByTagName('rows')[0];
        l = rows.getElementsByTagName('row').length;
        while (i < l) {
            var request = rows.getElementsByTagName('row')[i];
            var num = request.getElementsByTagName("cell")[0].childNodes[0].nodeValue;
            var name = request.getElementsByTagName("cell")[1].childNodes[0].nodeValue;
            var address = request.getElementsByTagName("cell")[2].childNodes[0].nodeValue;
            var time = request.getElementsByTagName("cell")[3].childNodes[0].nodeValue;
            var ag = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
            var dp = request.getElementsByTagName("cell")[5].childNodes[0].nodeValue;
            var e = request.getElementsByTagName("cell")[6].childNodes[0].nodeValue;
            var pl = request.getElementsByTagName("cell")[7].childNodes[0].nodeValue;
            var oth = request.getElementsByTagName("cell")[8].childNodes[0].nodeValue;
            var tank = request.getElementsByTagName("cell")[9].childNodes[0].nodeValue;
            var eq = request.getElementsByTagName("cell")[10].childNodes[0].nodeValue;
            var number_visit = request.getElementsByTagName("cell")[11].childNodes[0].nodeValue;
            var id = request.getElementsByTagName("cell")[12].childNodes[0].nodeValue;
            var status = request.getElementsByTagName("cell")[13].childNodes[0].nodeValue;
            var contact = request.getElementsByTagName("cell")[14].childNodes[0].nodeValue;
//            var file_name = request.getElementsByTagName("cell")[4].childNodes[0].nodeValue;
//            file_name = file_name.split("(driver)")[1];
//            var extra_path="";
//            if(file_name) {
//                file_name = file_name.split(".")[0];
//                extra_path =
//            }

            row = document.getElementById('driver_list_table').insertRow(-1);
            row.insertCell(-1).innerHTML = '<button id="button_for_'+id+'" onclick="driver_done('+id+')">Выполнено</button>';
            row.insertCell(-1).innerHTML = '<button onclick="driver_cancel('+id+')">Оформить перенос</button>';
            if(number_visit == "0") {
                row.insertCell(-1).innerHTML = '<div style="color:red" title="Построение маршрута не было выполнено">' + " ---- " + '</div>';
            }else{
                row.insertCell(-1).innerHTML = number_visit;
            }
            row.insertCell(-1).innerHTML = num;
            row.insertCell(-1).innerHTML = name;
            row.insertCell(-1).innerHTML = address;
            row.insertCell(-1).innerHTML = contact;
            row.insertCell(-1).innerHTML = time;
            row.insertCell(-1).innerHTML = ag;
            row.insertCell(-1).innerHTML = dp;
            row.insertCell(-1).innerHTML = e;
            row.insertCell(-1).innerHTML = pl;
            row.insertCell(-1).innerHTML = oth;
            row.insertCell(-1).innerHTML = tank;
            row.insertCell(-1).innerHTML = eq;
            if(status == "2"){
                $(row).find("button").prop('disabled', true);
                $(row).css('background-color', 'rgb(197, 197, 197)');
                $(row).find("button").eq(1).css('background-color', 'rgb(197, 197, 197)');
            }
            if(status == "1" || status == "3"){
                $(row).find("button").prop('disabled', true);
                $(row).css('background-color', 'rgb(197, 197, 197)');
                $(row).find("button").eq(0).css('background-color', 'rgb(197, 197, 197)');
            }
            i++;
        }
//        update_xlsx();
//        update_delete_links();

    }
    function driver_done(id){
        $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
            function () { // пoсле выпoлнения предъидущей aнимaции
                $('#modal_form #order_id').val(id);
                $('#modal_form')
                    .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                    .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
            });
        var a = 1;
    }
    function driver_cancel(id) {
        var a = 1;
        $('#modal_form2 #order_id2').val(id);
        $('#overlay').fadeIn(400, // снaчaлa плaвнo пoкaзывaем темную пoдлoжку
            function () { // пoсле выпoлнения предъидущей aнимaции
//                $('#modal_form2 #order_id').val(id);
                $('#modal_form2')
                    .css('display', 'block') // убирaем у мoдaльнoгo oкнa display: none;
                    .animate({opacity: 1, top: '50%'}, 200); // плaвнo прибaвляем прoзрaчнoсть oднoвременнo сo съезжaнием вниз
            });
    }

    function driver_done_save(){
        var tank = $("#modal_form #tank").val();
        var id = $("#modal_form #order_id").val();
        if($.isNumeric(tank)){
            $(".error").hide();
            navigator.geolocation.getCurrentPosition(
                // Обработчик успшеного получения координат
                function (position) {
//                    alert("Координаты: " + position.coords.longitude + ", " + position.coords.latitude);
                    $.ajax({
                        type: "POST",
                        data: {
                            driver_done:"",
                            order_id: id,
                            tank: tank,
                            comment: $("#modal_form #comment").val(),
                            coords_longitude: position.coords.longitude,
                            coords_latitude: position.coords.latitude
                        },
                        url: "/iwaterTest/backend_driver.php",
                        success: function (req) {
                            var tr = $("#button_for_"+id).parent().parent();
                            tr.find("button").prop('disabled', true);
                            tr.css('background-color', 'rgb(197, 197, 197)');
                            tr.find("button").eq(1).css('background-color', 'rgb(197, 197, 197)');
                            $("#overlay").click();
//
                        }
                    });
                },

                // Оработчик неудачного завершения получения коордиант
                function (error) {
                    alert("При определении координат произошла ошибка. Ее код: " + error.code);
                    $.ajax({
                        type: "POST",
                        data: {
                            driver_done:"",
                            order_id: id,
                            tank: tank,
                            comment: $("#modal_form #comment").val(),
                            coords_longitude: "Код ошибки браузера",
                            coords_latitude: error.code
                        },
                        url: "/iwaterTest/backend_driver.php",
                        success: function (req) {
                            var tr = $("#button_for_"+id).parent().parent();
                            tr.find("button").prop('disabled', true);
                            tr.css('background-color', 'rgb(197, 197, 197)');
                            tr.find("button").eq(1).css('background-color', 'rgb(197, 197, 197)');
                            $("#overlay").click();
//
                        }
                    });
                },

                // Параметры
                {
                    enableHighAccuracy: false,     // Режим получения наиболее точных данных
                    timeout: 10000,                // Максиальное время ожидания ответа (в миллисекундах)
                    maximumAge: 1000               // Максимальное время жизни полученных данных
                });
        }else{
            $(".error").show();
        }


    }

    function driver_cancel_save() {
        var id = $("#modal_form2 #order_id2").val();
        var val = $("#driver_cancel_form input[name='group1']:checked").val();
        var extra_val = "none";
        if (val == "Другое") {
            extra_val = $("#driver_cancel_form textarea").val();
        }
        var val2 = $("#driver_cancel_form input[name='group2']:checked").val();
        if (typeof val == "undefined" || typeof val2 == "undefined" || extra_val == "") {
            $(".error").show();
            return 0;
        } else {

            $(".error").hide();

            navigator.geolocation.getCurrentPosition(
                // Обработчик успшеного получения координат
                function (position) {
//                    alert("Координаты: " + position.coords.longitude + ", " + position.coords.latitude);
                    $.ajax({
                        type: "POST",
                        data: {
                            driver_cancel: "",
                            order_id: id,
                            reason: val,
                            agreed: val2,
                            comment: extra_val,
                            coords_longitude: position.coords.longitude,
                            coords_latitude: position.coords.latitude
                        },
                        url: "/iwaterTest/backend_driver.php",
                        success: function (req) {
                            var tr = $("#button_for_" + id).parent().parent();
                            tr.find("button").prop('disabled', true);
                            tr.css('background-color', 'rgb(197, 197, 197)');
                            tr.find("button").eq(0).css('background-color', 'rgb(197, 197, 197)');
                            $("#overlay2").click();
//
                        }
                    });
                },

                // Оработчик неудачного завершения получения коордиант
                function (error) {
                    alert("При определении координат произошла ошибка. Ее код: " + error.code);
                    $.ajax({
                        type: "POST",
                        data: {
                            driver_cancel: "",
                            order_id: id,
                            reason: val,
                            agreed: val2,
                            comment: extra_val,
                             coords_longitude: "Код ошибки браузера",
                               coords_latitude: error.code
                        },
                        url: "/iwaterTest/backend_driver.php",
                        success: function (req) {
                            var tr = $("#button_for_" + id).parent().parent();
                            tr.find("button").prop('disabled', true);
                            tr.css('background-color', 'rgb(197, 197, 197)');
                            tr.find("button").eq(0).css('background-color', 'rgb(197, 197, 197)');
                            $("#overlay2").click();
//
                        }
                    });
                },

                // Параметры
                {
                    enableHighAccuracy: false,     // Режим получения наиболее точных данных
                    timeout: 10000,                // Максиальное время ожидания ответа (в миллисекундах)
                    maximumAge: 1000               // Максимальное время жизни полученных данных
                });

        }

    }
</script>
