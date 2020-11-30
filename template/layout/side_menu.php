<?php $perms = get_perms() ?>
<div class="left_ul_cont">
<div class="left_ul">
<ul>
   <li>
      <a href="/iwaterTest/">Главная</a>
   </li>

   <?php if(isset($perms['add_company'])) {?>
      <li class="menu_sub">
         <a href="#">Компании</a>
         <ul>
            <?php if(isset($perms['list_companies'])) {?><li><a href="/iwaterTest/admin/list_companies/">Список компаний</a></li><?php } ?>
            <?php if(isset($perms['add_company'])) {?><li><a href="/iwaterTest/admin/add_company/">Добавить компанию</a></li><?php } ?>
            <!--<li><a href="/iwaterTest/admin/add_company/">Редактировать компанию</a></li>-->
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['list_clients'])) {?>
      <li class="menu_sub">
         <a href="#">Клиенты</a>
         <ul>
            <?php if(isset($perms['list_clients'])) {?><li><a href="/iwaterTest/admin/list_clients/">Список клиентов</a></li><?php } ?>
            <?php if(isset($perms['add_client'])) {?><li><a href="/iwaterTest/admin/add_client/">Добавить клиента</a></li><?php } ?>
            <!--<li><a href="/iwaterTest/admin/edit_clients/">Редактировать клиента</a></li>-->
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['list_orders_app'])) {?>
      <li class="menu_sub">
         <a href="#">Заказы</a>
         <ul>
            <?php if(isset($perms['list_orders'])) {?><li><a href="/iwaterTest/admin/list_orders/">Список заказов</a></li><?php } ?>
            <?php if(isset($perms['add_order'])) {?><li><a href="/iwaterTest/admin/add_order/">Добавить заказ</a></li><?php } ?>
            <?php if(isset($perms['list_orders_app'])) {?><li><a href="/iwaterTest/admin/list_orders_app/">Необработанные заказы</a></li><?php } ?>
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['list_unit'])) {?>
      <li class="menu_sub">
         <a href="#">Номенклатура</a>
         <ul>
            <?php if(isset($perms['list_unit'])) {?><li><a href="/iwaterTest/admin/list_unit/">Список номенклатуры</a></li><?php } ?>
            <?php if(isset($perms['add_unit'])) {?><li><a href="/iwaterTest/admin/add_unit/">Добавить товар</a></li><?php } ?>
            <!--<li><a href="/iwaterTest/admin/list_unit/#editItem">Редактировать товар</a></li>-->
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['analytics'])) {?>
      <li class="menu_sub">
         <a href="#">Статистика</a>
         <ul>
           <!-- <li><a href="/iwaterTest/admin/analytics/?tab=tab-container">Статистика водителей</a></li>-->
           <?php if(isset($perms['driver_stat'])) {?><li><a href="/iwaterTest/admin/driver_stat">Статистика водителей</a></li><?php } ?>
            <?php if(isset($perms['analytics'])) {?><li><a href="/iwaterTest/admin/analytics">Предиктиваная аналитика</a></li><?php } ?>
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['driver_list'])) {?>
      <li class="menu_sub">
         <a href="#">Путевые листы</a>
         <ul>
            <?php if(isset($perms['trav_list'])) {?><li><a href="/iwaterTest/admin/trav_list/">Список путевых листов</a></li><?php } ?>
            <?php if(isset($perms['trav_list'])) {?><li><a href="/iwaterTest/admin/trav_list/">Добавить путевой лист</a></li><?php } ?>
            <?php if(isset($perms['driver_map'])) {?><li><a href="/iwaterTest/admin/driver_map/">Карта водителя</a></li><?php } ?>
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['add_provider'])) {?>
      <li class="menu_sub">
         <a href="#">Поставщики</a>
         <ul>
            <?php if(isset($perms['list_provider'])) {?><li><a href="/iwaterTest/admin/list_provider/">Список поставщиков</a></li><?php } ?>
            <?php if(isset($perms['add_provider'])) {?><li><a href="/iwaterTest/admin/add_provider/">Добавить поставщиков</a></li><?php } ?>
         </ul>
      </li>
   <?php } ?>
   <?php if(isset($perms['storage_control'])) {?>
      <li class="menu_sub">
         <a href="#">Склады</a>
         <ul>
           <?php if(isset($perms['add_storage'])) {?><li><a href="/iwaterTest/admin/add_storage/">Добавить склад</a></li><?php } ?>
            <?php if(isset($perms['storage_control'])) {?><li><a href="/iwaterTest/admin/storage_control/">Управление складами</a></li><?php } ?>
            <?php if(isset($perms['storage_arrival'])) {?><li><a href="/iwaterTest/admin/storage_arrival/">Создать движение по складам</a></li><?php } ?>
            <?php if(isset($perms['storage_info'])) {?><li><a href="/iwaterTest/admin/storage_info/">Информация о складах</a></li><?php } ?>
         </ul>
      </li>
   <?php } ?>
</ul>
</div>
<div class="left_ul_hide">
   <img src="/iwaterTest/css/image/icon_up.png" style="margin: 7px;">
</div>
</div>

<style>
   .left_ul_cont {
      display: block;
      max-width: 210px;
      max-height: 500px;
   }
   .left_ul {
      width: 210px;
      height: 450px;
      float: left;
      background-color: #fff;
      border-radius: 7px;
   }
   .left_ul ul {
      margin-top: 0px;
      /* padding-top: 28px; */
      padding-left: 1px;
   }
   .left_ul > ul > li {
      list-style-type: none;
      padding: 0 0 13px 0;
   }

   .left_ul > ul > li:first-child {
      list-style-type: none;
      padding: 30px 0px 13px 0px;
   }

   .left_ul > ul > li > a {
      color: #000;
      text-decoration: unset;
      padding-left: 28px;
      font: normal 17px Tahoma;
   }
   .left_ul > ul > li > a:hover,
   .left_ul > ul > li > a.active {
       color:#015aaa;
   }
   .left_ul .menu_sub  ul {
       display: none;
       overflow: hidden;
   }
   .left_ul .menu_sub  ul li {
       list-style-type: none;
       padding: 3px 0px 3px 41px;
   }
   .left_ul .menu_sub ul li a {
      color: #000;
      text-decoration: unset;
      font: normal 10px Tahoma;
   }

   .left_ul_hide {
      width: 35px;
      height: 20px;
      border-radius: 0px 0 10px 10px;
      padding: 0 1px 7px 0;
      margin-left: 20px;
      font-size: 30px;
      background-color: #74ccea;
      color: #fff;
      float: left;
      font-family: Open Sans;
      font-stretch: ultra-expanded;
      position: inherit;
      text-align: center;
   }

   .left_ul_hide:hover {
      background: #015aaa;
      cursor: pointer;
   }
</style>

<script type="text/javascript">
$(document).ready(function () {
   // СВЁРТЫВАНИЕ И РАЗВОРАЧИВАНИЕ ПОДКАТЕГОРИЙ В БОКОВОМ МЕНЮ
    $('.menu_sub > a').click(function(){
      $('.menu_sub ul').slideUp();
       if ($(this).next().is(":visible")){
           $(this).next().slideUp();
       } else {
       $(this).next().slideToggle();
       }
      return false;
    });
       $('.left_ul > ul > li > a').click(function(){
	   $('.left_ul > ul > li > a, .menu_sub a').removeClass('active');
	   $(this).addClass('active');
	}),
       $('.menu_sub ul li a').click(function(){
	   $('.menu_sub ul li a').removeClass('active');
	   $(this).addClass('active');
	});

   // СКРЫТЬ ИЛИ РАЗВЕРНУТЬ БОКОВОЕ МЕНЮ
   $('.left_ul_hide').click(function(){
     if ($('.left_ul').css('display') == 'none') {
         $('.left_ul_hide').html('<img src="/test/css/image/icon_up.png" style="margin: 7px;">');
         $('.main').animate({
            width: '80%',
            margin: '0 0 0 200px',
            padding : '0 0 0 20px'
         }, 300, "swing");
         $('#page').animate({
            width: '1280px',
            margin: '0 0 0 -640px',
            left : '50%'
         }, 300, "swing");
         setTimeout(function() {
           $('.left_ul').slideDown({
              duration: 300,
              easing: "swing"
           });
        }, 300);
     } else {
        $('.left_ul').slideUp({
           duration: 300,
           easing: "swing",
           done: function() {
              $('.left_ul_hide').html('<img src="/test/css/image/icon_down.png" style="margin: 7px;">');
              $('.main').animate({
                 width: '98%',
                 margin: '0px',
                 padding : '0px auto'
              }, 300, "swing");
              $('#page').animate({
                 width: '100%',
                 margin: '0px',
                 left : '0'
              }, 300, "swing");
           }
       });
     }
   });

});


</script>
