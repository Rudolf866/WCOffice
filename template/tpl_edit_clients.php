<?php
   $id = trim(filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS));
   $client = get_client_by_id($id);
   $i = 0; $j = "";
?>

<div class="main">
   <div id="add_client_form" class="add_client_form">
      <div class="info">
         <div class="name_title">
            <div class="name_position">Редактировать клиента</div>
         </div>

         <form id="content_add" class="main_form" method="post" action="/iwaterTest/backend.php">
            <div class="content_add" style="display: grid;">
               <div class="left">

                  <input name="id_db" type="hidden" value="<?php echo $client[0]['id'] ?>">
                  <label style="width: 180px;"><input class="classic" id="type_0" name="type" type="radio" value="0"> Физическое лицо</label> <label style="width: 180px;"><input class="classic" id="type_1" name="type" type="radio" value="1" style="margin-left: 20px;">
                     Юридическое лицо </label> <br>
                  <label id="label_name" for="name" style="width: 100px">Имя</label>
                  <input name="name" type="text" placeholder="Имя" value="<?php echo $client[0]['name'] ?>" style="width: 245px;margin-right: 5px;">
                   <label for="num_c" style="width: 100px">Номер клиента</label><input name="num_c" type="text" placeholder="№ клиента" class="client_id" value="<?php echo $client[0]['client_id'] ?>" style=" width: 245px;">
                  <br>
                   <label for="phone" style="width: 100px">Телефон клиента</label><input name="phone" type="text" placeholder="Телефон клиента" class="phone" value="<?php echo $client[0]['phone'] ?>" style=" width: 245px;">
                   <br>
               </div>
               <?php while($i < count($client)) { ?>
               <div id="point" class="left" value="<?php echo $client[$i]['a_id']; ?>">
                     <label for="contact[]" style="width: 100px;">Доп.инфо</label><input id="contact_<?echo $j ?>" name="contact[]" class="contact" type="text" placeholder="Доп.инфо" value='<?php echo stripcslashes($client[$i]['contact']) ?>' style="width: 245px;height: 200px;"><input type="button"
                        class="btn_add" id="add_objects" value="+">

                  <br>
                     <label for="region[]" style="width: 100px; ">Регион </label>

                     <select id="region_<?echo $j ?>" class="add_client_region" name="region[]" style="width: 250px; ">
<!--                        --><?php //echo get_regions_selected(get_number_region($client[$i]['region'])) ?>
                         <?php echo select_regions(); ?>
                     </select>
                     <br>
                     <label for="address[]" style="width: 100px; ">Адрес</label><textarea id="address_<?echo $j ?>" class="add_client_address" name="address[]" type="text" onfocus="getFocus(this);" placeholder="Адрес" style="width: 245px; vertical-align: middle; "><?php echo $client[$i]['address'] ?></textarea>
                     <input type="button" class="btn_add" onclick="add_address();check_point(this);" id="add_objects" value="+" style=" margin: 5px 0px 5px 0px;text-align: center;">
                     <a href="#" onclick="add_address();" style="margin-right: 10px;">Добавить адрес</a>
                     <a onclick="delete_address(this);" style="text-decoration: underline; cursor: pointer;">Удалить адрес</a>
                     <input class="coords add_client_cords" id="cords_<?echo $j ?>" value="<?php echo $client[$i]['coords'] ?>" name="cords[]" type="hidden">
                     <input type="hidden" class="classic" onclick="setMarker(this);" value="Восстановить координаты"> <br>
                     <a href="#" class="classic" onclick="setMarker(this);" style="margin-right: 10px;">Восстановить координаты </a>
                     <a href="#" onclick="if($('#hidemap').css('display')!='none'){$('#hidemap').hide();}else{$('#hidemap').fadeIn().css( 'display', 'inline-block');;}">Карта</a>
                     <br>
                     <br>
               </div>
               <?php
                  $i++;
                  $j++;
               }
               ?>
            </div>
            <input name="id_db" value="<?php echo $_GET['id'] ?>" type="hidden">
            <input class="classic search_button" id="submit" name="edit_client" type="submit" value="Сохранить" style="float: right;">
            <input class="classic reset_button" id="submit" name="submit" type="submit" value="Отменить" style="float: right;">
         </form>
      </div>
      <div id="hidemap">
         <div id="map"></div>
      </div>
   </div>
</div>

<script type="text/javascript">

   
    function initMap() {
                     myMap = new google.maps.Map(document.getElementById('map'), {
         center: {lat: 59.93, lng: 30.31},
         zoom: 10
      });
                 }
    var myMap;
    var coords;
    var myCollection;
    var count_addr = $(".point").length;
    if(count_addr == 1){
        var id = '';
    }else{
        id = count_addr - 1;
    }

    var j = '';
    var k='';                                               
    var array_cords = [];
    var check_address_keyup = false;
    var cords_input='';

    function check_point(el) {
        check_address_keyup = false;
        $(el).parent().find(".classic")[0].value = "Восстановить координаты";
        var addr_input = $(el).parent().find(".add_client_address");
        var region_input = $(el).parent().find(".add_client_region");
        cords_input = $(el).parent().find(".add_client_cords");
        var address_num = addr_input.attr('id');
        address_num = address_num.split('_');

      var address = 'Россия ' + region_input.val() + ' ' + addr_input.val();
      getCoords(address,setCoords);
    }

    function setCoords(coords) {
   cords_input.val(coords[1] + ',' + coords[0]);
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
function setMarker(el){
    cords_input = $(el).parent().find(".add_client_cords");
   var coords = cords_input.val().split(',');
       var gooCoord = new google.maps.LatLng(coords[0], coords[1]);
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
        atest = $('#point').clone();
        id++;

        atest.find('.contact').val('').css('margin', '0 1px 12px 0px');
        atest.find('.add_client_region').val('Санкт-Петербург').css('margin', '0 1px 12px 0px');
        atest.find('.add_client_address').val('').css('margin', '0 1px 12px 0px');
        atest.find('.add_client_cords').val('').css('margin', '0 1px 12px 0px');

        $("#submit").before(atest);
        jQuery.validator.addClassRules('add_client_address', {
            ableAddress: true,
            notEqualCords: true
        });
        $('.add_client_address').change(function(){
            check_point(this);
        }); 
        $(".delete.edit_client").show();
        $(".delete.edit_client:first").hide();
    }

    function delete_address(arg) {
       var selectedAddress = arg.parentNode.childNodes[14].value;

       $.ajax({
            type: 'POST',
            url: '/iwaterTest/backend.php',
            data: {
               delete_client_address: selectedAddress,
               client_id: $('.client_id').val()
            },
            success: function() {
               document.location.reload();
            }
        });
    }

    
    $(document).ready(function () {
        jQuery.validator.addMethod("unique_with_current_num", function (value, element) {
            var result = false;
            result = unique_with_current_num(value, element);

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
        }, 'Координаты не сформировались, нажмите кнопку '/*"Добавить кординаты"или */+'"Восстановить координаты"');

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
            return result; //Если адресса нет - нет и координат, если адрес есть, а координат нет, то ошибка, если есть и то, и то, все норм.
        }, 'Есть повторяющиеся координаты! Проверте и измените адреса, либо удалите один из адресов');


        $("#add_client_form form").validate({
            rules: {
                type: {
                    required: true
                },
                name: {
                    required: true
                },
                num_c: {
                    required: true,
                    digits: true,
                    unique_with_current_num: true
                },
                phone: {
                    required: true,
                    // digits: true,
                    unique_with_current_num: true
                }

            },
            messages: {
                type: {
                    required: "Выбирите вид субъекта"
                },
                name: {
                    required: "Заполните имя"
                },
                num_c: {
                    required: "Вернуть обратно испавления не должны быть!",
                    digits: "Может быть только числом"
                },
                phone: {
                    required: "Введите номер",
                    unique: true
                    // digits: "Может быть только числом"
                }
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            }
        });
        jQuery.validator.addClassRules('add_client_address', {
            ableAddress: true,
            notEqualCords: true
        });

    });
    //function unique_with_current_num(value, element) {
    //    var name = element.name;
    //    $.ajax({
    //        type: "POST",
    //        async: false,
    //        data: {
    //            unique: "client_id",
    //            type: name,
    //            current_id: <?php //echo $client[0]['client_id']?>//,
    //            value: value
    //        },
    //        url: "/iwaterTest/backend.php",
    //        success: function (count) {
    //            result = (count != "") ? false : true;
    //        }
    //    });
    //    return result;
    //}
    function unique_with_current_num(value, element) {
        var name = element.name;
        $.ajax({
            type: "POST",
            async: false,
            data: {
                unique: "phone",
                type: name,
                current_id: <?php echo $client[0]['phone']?>,
                value: value
            },
            url: "/iwaterTest/backend.php",
            success: function (count) {
                result = (count != "") ? false : true;
            }
        });
        return result;
    }
    var default_address = '';
    function getFocus(el){
        default_address = $(el).val();
        console.log(default_address);
    }
    $('.add_client_address').keyup(function(){
        check_address_keyup = true;
    });
    $('.add_client_address').focusout(function(){
        if(check_address_keyup == true) {
            var result = confirm('Сменить адрес на введенный?');
            if(result){
            check_point(this);
            $('#hidemap').fadeIn().css("display", "inline-block");
        }else{
            $(this).val(default_address);
        }
    }
    });

    function delete_point(elem){
        id--;
        elem.parent().detach();
    }
    $(function(){
        <?php if($client[0]['type']=="0"){ ?>
            $('#type_0').attr('checked', true);
        <?php }else{ ?>
             $('#type_1').attr('checked', true);
        <?php } ?>
        $(".delete.edit_client:first").hide();
    })
</script>

<style type="text/css">
    #content_add {
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
    .point .contact{
        margin-left: 47px;
    }
    .add_client_region{
        margin-left: 54px;
    }
    .add_client_address{
        margin-left: 63px;
    }

</style>
