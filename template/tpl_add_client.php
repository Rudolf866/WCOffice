<?php
$ID = get_insert_id();
$InID = intval($ID['id']);
$InID++;
$company = get_company_coords();
$array = explode(',', $company['coords']);
?>
<style type="text/css">
    .content_add {
    background-color: #fff;
    border-radius: 9px;
    width: 800px;
    margin-top: 10px;
    overflow: hidden;
    padding: 20px;
    }


    .main_form >.content_add>.point label {
        float: none !important;
        width: 100% !important;
    }       

    .left {
        float: left;
        width: 50%;
        position: relative;
    }

    .right {
        display: inline-block;
        margin-top: 11px;
    	text-align: right; 
    	position: relative;       
    }

    .right label {
		text-align: left;
    }

    .right a:nth-child(1){
    	display: block;
    	top:0;
    	right: 60px;
    	position: absolute;
    }

    .right a:nth-child(2){
    	display: block;
    	top: 0;
    	right: 0;
    	position: absolute;
    }

    .left label:nth-child(4){
        width: 100%;
    }

    .right> label,
    .right> input{
        margin-top: 30px;
    }

    .point label,
    .point input {
    	margin-top: 0px;
    }

    .btn_add {
    	background: url(image/add.png) no-repeat center;
    	width: 20px;
    	height: 20px;
		color: #015aaa;
    	padding: 0;
    	font-size: 20px;
    	vertical-align: middle;
    	outline: 0 !important;
    	border: none;
    }

    #num_c-error {
        position: absolute;
        left: 88px !important;
        top: 20px !important;
    }


    #type-error {
        position: absolute;
        left: -280px !important;
        top: -23px !important;
    }

    #name-error {
        position: absolute;
        top: 11px !important;
        left: 93px !important;
    }

    select {
        width: 252px !important;
    }

</style>
<div class="main">
    <div id="add_client_form" class="add_client_form">
        <div class="info">
        <form id="add_client_form" class="main_form" method="post" action="/iwaterTest/backend.php">
            <div class="name_title">
                <div class="name_position">Добавить клиента</div>
            </div>
            <div class="content_add">
                    <div class="left">
                        <label style="width: 180px;"><input class="classic" name="type" type="radio" value="0"> Физическое лицо</label> <label style="width: 180px;"><input class="classic" name="type" type="radio" value="1" style="margin-left: 20px;"> Юридическое лицо </label> <br>
                        <label id="" for="" style="width: 100px">Клиент id:</label>
                        <input name="" type="text" placeholder="<?php echo $InID;?>" style="width: 245px;margin-right: 5px;" READONLY><br>
                        <label id="label_name" for="name" style="width: 100px">Имя</label>
                        <input name="name" type="text" placeholder="Имя" style="width: 245px;margin-right: 5px;">
<!--                        <label for="num_c" style="width: 100px">Номер клиента</label><input name="num_c" type="text" placeholder="№ клиента" style="width: 245px;">-->
                        <label for="contact" style="width: 100px">Телефон</label><input id="contact_" name="contact" type="text" placeholder="Телефон" style="width: 245px;"><input type="button" class="btn_add" id="add_objects" value="+"><br>
                        <label for="information" style="width: 100px">Дополнительная информация</label><input id="information" name="information" type="text" placeholder="Для путевых листов" style="width: 245px;height: 200px;">
                    </div>
                    <div class="right">
                        <a href="#" class="classic" onclick="check_point(this);/*add_placemarks();*/">Восстановить координаты </a>
                        <a href="#"
                   onclick="if($('#hidemap').css('display')!='none'){$('#hidemap').hide();}else{$('#hidemap').fadeIn().css( 'display', 'inline-block');/*add_placemarks();*/;}">Карта</a> <br>
<!--                    <label id="label_name" for="name" style="width: 100px">Имя</label>-->
<!--                    <input name="name" type="text" placeholder="Имя" style="width: 245px;margin-right: 5px;">-->
                    <div id="point" class="point">

                <label for="region[]" style="width: 100px">Регион </label>
                <select id="region_" class="add_client_region" name="region[]">
<!--                    <option value="Санкт-Петербург" selected>Санкт-Петербург</option>-->
<!--                    <option value="Колпино">Колпино</option>-->
<!--                    <option value="Пушкин">Пушкин</option>-->
<!--                    <option value="Красное Село">Красное Село</option>-->
<!--                    <option value="Металлострой">Металлострой</option>-->
<!--                    <option value="Павловск">Павловск</option>-->
<!--                    <option value="Шушары">Шушары</option>-->
<!--                    <option value="Горелово">Горелово</option>-->
<!--                    <option value="Коммунар">Коммунар</option>-->
<!--                    <option value="Стрельна">Стрельна</option>-->
<!--                    <option value="Петергоф">Петергоф</option>-->
<!--                    <option value="Ломоносов">Ломоносов</option>-->
<!--                    <option value="Кронштадт">Кронштадт</option>-->
<!--                    <option value="Ленинградская область">Ленинградская область</option>-->
                    <?php echo select_regions(); ?>

                </select>
                <br>
                <label for="address[]" style="width: 100px">Адрес</label><textarea id="address_" class="add_client_address" name="address[]" type="text" placeholder="Адрес" style="width: 245px; vertical-align: middle;"></textarea><input type="button" class="btn_add" id="add_objects" value="+" style="position: absolute;">
                <br>
                <input class="coords add_client_cords" id="cords_" name="cords[]" type="hidden">
            </div>
            <input name="add_client" type="hidden">

        		</div>                    
            </div>
            <input class="classic search_button" id="submit" name="submit" type="submit" value="Добавить" style="float: right;">
            <input class="classic reset_button" id="submit" name="submit" type="submit" value="Создать" style="float: right;">
        </form>
        </div>
        <div id="hidemap">
            <div id="map"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var myMap;
    var coords;
    var myCollection;
    var id = '';
    var j = '';
    var k='';
    var array_cords = [];

    var check_address_keyup = false;
    var cords_input = '';

    function initMap() {
                     myMap = new google.maps.Map(document.getElementById('map'), {
         center: {lat: <?php echo $array[0]; ?>, lng: <?php echo $array[1]; ?>},
         zoom: 10
      });
                 }

    function check_point(el) {
        check_address_keyup = false;
        $(el).parent().find(".classic")[0].value = "Восстановить координаты";
        var addr_input = $(el).parent().find(".add_client_address");
        var region_input = $(el).parent().find(".add_client_region");
        var cords_input = $(el).parent().find(".add_client_cords");
        var pointCoord;

      var address = 'Россия ' + region_input.val() + ' ' + addr_input.val();
      getCoords(address,setCoords);
    }

    function setCoords(coords) {
  $('#cords_').val(coords[1] + ',' + coords[0]);
  console.log( $('#cords_').val());
   var gooCoord = new google.maps.LatLng(coords[1], coords[0]);
            var image = {
               url: 'http://iwatercrm.ru/iwaterTest/css/image/yellow.png',
               scaledSize: new google.maps.Size(40, 40)
            };

            
            myPlacemark = new google.maps.Marker({
               position: gooCoord,
               map: myMap,
               icon: image,
               draggable: false
            });

}

    function add_address() {
        atest = jQuery('#point').clone();
        id++;
        atest[0].childNodes[1].id = atest[0].childNodes[1].id + id;
        atest[0].childNodes[2].id = atest[0].childNodes[2].id + id;
        atest[0].childNodes[6].id = atest[0].childNodes[6].id + id;
        atest[0].childNodes[9].id = atest[0].childNodes[9].id + id;
        atest[0].childNodes[18].id = atest[0].childNodes[18].id + id;
        atest[0].childNodes[1].value = '';
        atest[0].childNodes[2].value = '';
        atest[0].childNodes[6].value = '';
        atest[0].childNodes[9].value = '';
        atest[0].childNodes[18].value = '';
        $("#submit").before(atest);
        jQuery.validator.addClassRules('add_client_address', {
            ableAddress: true,
            notEqualCords: true
        });
        $('.add_client_address').change(function(){
            check_point(this);
            add_placemarks();
        });
        $(".delete.add_client").show();
        $(".delete.add_client:first").hide();

        jQuery.validator.addClassRules('add_client_region', {
            check_region: true
        });
        //console.log(atest[0].childNodes[1]);
    }
    $(document).ready(function () {
        jQuery.validator.addMethod("unique", function (value, element) {
            var result = false;
            result = check_unique(value, element);

            return result;
        }, "Такой клиент уже существует");
        jQuery.validator.addMethod("ableAddress", function (value, element) {
            var result = true;
            var addresses = $(".add_client_address").length;
            for(var k = 0; k<addresses;k++){
                var addr = $(".add_client_address")[k];
                if(addr.value != ""){
                    result = false;
                    addr = $(".add_client_address").eq(k);
                    var cords = addr.parent().children(".add_client_cords");
                    if(cords.val() == "" || typeof cords.val() == "undefined"){
                        return result;
                    }else{
                        result = true;
                    }
                }

            }
            return result; //Если адресса нет - нет и координат, если адрес есть, а координат нет, то ошибка, если есть и то, и то, все норм.
        }, 'Координаты не сформировались, нажмите кнопку "Восстановить координаты"');

        jQuery.validator.addMethod("notEqualCords", function (value, element) {
            var result = true;
            var arr_cords = $(".add_client_cords");
            var flag;
            for(var k = 0; k<arr_cords.length;k++){
                flag = 0;
                for(var k2 = 0; k2<arr_cords.length;k2++){
                    if(arr_cords.eq(k).val()==arr_cords.eq(k2).val()){
                        flag++;
                    }
                    if(flag >= 2){
                        return false;
                    }
                }
            }

//
            return result; //Если адресса нет - нет и координат, если адрес есть, а координат нет, то ошибка, если есть и то, и то, все норм.
        }, 'Есть повторяющиеся координаты! Проверте и измените адреса, либо удалите один из адресов');

        jQuery.validator.addMethod("check_region", function (value, element) {
            var result = true;
            var regions = $(".add_client_region").length;
            for(var k = 0; k<regions;k++){
                var addr = $(".add_client_region").eq(k);
                if(addr.val() == null){
                    result = false;
                }

            }
            return result; //Если адресса нет - нет и координат, если адрес есть, а координат нет, то ошибка, если есть и то, и то, все норм.
        }, 'Заполните все поля с регионом');
        $("#add_client_form form").validate({
            onfocusout: false,
            onkeyup: false,
            onclick: false,
            rules: {
                type: {
                    required: true
                },
                name: {
                    required: true
                },
                contact: {
                    required: true,
                    // digits: false,
                    unique: true
                }
            },
            messages: {
                type: {
                    required: "Выбирите вид субъекта"
                },
                name: {
                    required: "Заполните имя"
                },
                contact: {
                    required: "Введите номер телефона",
                    // digits: "Может быть только числом"
                }
            },
            errorPlacement: function(error, element) {
                if (element.attr("name") == "type")
                {
                    error.insertBefore($("#label_name"));
                }
                else if (element.attr("class") == "add_client_region")
                {
                    error.insertAfter($(".add_client_region:first-child"));
                }
                else
                {
                    error.insertAfter(element);
                }
            }
        });
        jQuery.validator.addClassRules('add_client_address', {
            ableAddress: true,
            onfocusout: false,
            onkeyup: false,
            onclick: false
        });

    });
    function check_unique(value, element) {
        var name = element.name;
        $.ajax({
            type: "POST",
            async: false,
            data: {
                unique: "contact",
                type: name,
                value: value
            },
            url: "/iwaterTest/backend.php",
            success: function (count) {
                result = (count != "") ? false : true;
            }
        });
        return result;
    }
    $('.add_client_address').keyup(function(){
        check_address_keyup = true;
    });
    $('.add_client_address').focusout(function(){
        if(check_address_keyup == true) {
            check_point(this);
            add_placemarks();
            $('#hidemap').fadeIn().css("display", "inline-block");
        }
    });

    function add_placemarks(){
        var arr_address = $(".add_client_address");
        var arr_cords =  $(".add_client_cords");
//        var j = arr_address.length;

        myCollection.removeAll();
        for (var i = 0; i < arr_address.length; i++) {
            var cords = arr_cords.eq(i).val();
            cords= cords.split(",");
            myPlacemark = new ymaps.Placemark(
                [cords[0], cords[1]],
                {
                    iconContent: i+1,
                    hintContent:  "Адресс №". i+1,
                    address_num: i
                },
                {draggable: true});
            myCollection.add(myPlacemark);
            myMap.geoObjects.add(myCollection);
            myPlacemark.events.add("dragend", function (e) {
                var cords = e.get('target').geometry.getCoordinates();
                var address_num =  e.get('target').properties._data.address_num;
                if(address_num == 0) address_num = "";
                $('#cords_' + address_num).val(cords);
                k++;
            }, myPlacemark);
        }
    }


    function delete_point(elem){
        id--;
        elem.parent().detach();
        add_placemarks();
    }
    $(function(){
        $(".delete.add_client:first").hide();
    })
</script>
