<div class="main">
    <div class="main_form">
      <!-- ПУНКТ ДОБАВЛЕНИЯ ЗАКАЗОВ  -->
      <div class="category_label">
         Добавление пользователя
         <hr color="#e3e9f1" size="1px" width="850px">
      </div>
      <div class="add_client">
           <table class="add_client_table">
              <tr>
                 <td width="60px">Имя</td>
                 <td width="180px"><input class="newUserName" name="name" type="text" oninput="if ( this.value != '' ) { changeStatus++; }"> <input name="add_user" type="text" style="display: none;"> </td>
                 <td width="60px">Роль</td>
                 <td width="180px"><select name="role" class="selected_role" onchange="driverChecker();">
                     <?php echo select_role(); ?>
                 </select></td>
              </tr>
              <tr>
                 <td width="60px">Телефон</td>
                 <td width="180px"><input class="newUserPhone" name="phone" type="phone"></td>
                 <td width="60px" id="add_m_login_title"></td>
                 <td width="180px" id="add_m_login"></td>
              </tr>
              <tr>
                 <td width="60px">Логин</td>
                 <td width="180px"><input class="newUserLogin" name="login" type="text"></td>
                 <td width="60px" id="add_m_pass_title"></td>
                 <td width="180px" id="add_m_pass"></td>
              </tr>
              <tr>
                 <td width="60px">Пароль</td>
                 <td width="180px"><input class="newUserPassword" name="password" type="password"></td>
              </tr>
           </table>
            <input name="add_user" type="hidden">

            <div class="for_driver"></div>
            <input class="save_user" name="submit" type="submit" value="Добавить" onclick="createNewUser();" style="float: right;"><label class="save_user_label" for="save_user">Изменения были применены!</label>
            <input class="classic" name="submit" type="submit" style="background-color: #fff; color: #000; width: 160px;" value="Список пользователей" onclick="location.href = '/iwaterTest/admin/list_users'">
       </div>

       <!-- РЕДАКТИРОВАНИЕ ПЕРИОДОВ ДОСТАВКИ -->
       <div class="category_label">
         Период доставки
         <hr color="#e3e9f1" size="1px" width="850px">
      </div>
        <div class="period_list_cont">
           <div class="period_list">
             <div class="dev_email">
                 <label for="e_mail">E-mail  &#160; &#160; &#160; &#160;
                 <input id="e_mail" name="e_mail" type="text" placeholder="" value="<?php echo $e_mail_value; ?>"></label>
                 <div class="add_dev_email">+</div>
             </div>
             <label for="period">Добавить период</label>
             <div class="periods">
                 <input class="selected_period" name="period" input="text" id="period" placeholder="Название" oninput="if ( this.value != '' ) { changeStatus++; }">
                 <input type="text" class="choice_color" name="choice_color" data-palette='[{"primary": "#E91E63"},{"primary_dark": "#C2185B"},{"primary_light": "#F8BBD0"},{"accent": "#CDDC39"},{"primary_text": "#212121"},{"secondary_text": "#727272"},{"divider": "#B6B6B6"}]' placeholder="Цвет на картах" value="" onclick="showClicker();" style="margin-left: 10px;">
                 <div class="add_period_named" onclick="addNewPeriod();">+ Добавить</div>
             </div>
             <div class="list_current_periods"></div>
           </div>
           <input class="save_period" name="submit" type="submit" value="Сохранить" onclick="savePeriods();"><label class="save_period_label" for="save_period">Изменения были применены!</label>
           <input class="classic" name="submit" type="submit" style="background-color: #fff; color: #000" value="Отменить" onclick="cancelPeriods();">
        </div>

        <!-- РЕДАКТИРОВАНИЕ/ДОБАВЛЕНИЕ РОЛЕЙ -->
        <div class="category_label">
          Настройка ролей
          <hr color="#e3e9f1" size="1px" width="850px">
       </div>
       <div class="add_role_cont">
           <div class="add_role">
              <label for="role" style="line-height: 30px; margin-right: 30px;">Редактировать роль</label>
               <select class="edit_role" name="role" style="height: 30px;" onchange="selectRole( this.value ); $('.edit_for_select').val($(this).find('option:selected').text());">
                  <?php echo select_role(); ?>
               </select>
               <input class="edit_for_select" type="text" name="format" value="" />
               <a href="#" class="edit_role_links" onclick="deleteRole();">Удалить роль</a>
               <a href="#" class="edit_role_links" onclick="addRole();">Добавить роль</a>
               <input name="add_role" type="hidden">
               <div class="perms_list">

               </div>
            </div>
            <input class="add_role_b" name="submit" type="submit" value="Сохранить" onclick="saveRole();"><label class="edit_role_label" for="add_role_b">Изменения были применены!</label>
            <input class="classic" name="submit" type="submit" style="background-color: #fff; color: #000" value="Отменить" onclick="cancelRolesEdit();">
         </div>

         <!-- ПРЕДУПРЕЖДЕНИЕ О НЕХВАТКЕ ТОВАРОВ НА СКЛАДЕ -->
        <div class="category_label">
          Действие при нехватке товаров на складе
          <hr color="#e3e9f1" size="1px" width="850px">
        </div>
        <div class="storage_limit">
           <div class="on_limit_action">
              <label class="checkbox-conteiner" style="width: 250px !important;">
                 <input type="radio" name="storage_limiter" value="0" id="0" class="checkbox" >
                 <span class="checkbox-visual"></span>
                 <span class="checkbox-text">Запрет на добавление заказа</span>
              </label>
              <label class="checkbox-conteiner" style="width: 310px !important">
                 <input type="radio" name="storage_limiter" value="1" id="1" class="checkbox" >
                 <span class="checkbox-visual"></span>
                 <span class="checkbox-text">Предупреждение при добавление заказа</span>
              </label>
              <label class="checkbox-conteiner">
                 <input type="radio" name="storage_limiter" value="2" id="2" class="checkbox" >
                 <span class="checkbox-visual"></span>
                 <span class="checkbox-text">Игнорировать</span>
              </label>
           </div>
           <input class="add_role_b" name="submit" type="submit" value="Сохранить" onclick="saveOnLimitAction();"><label class="edit_role_label" for="add_role_b">Изменения были применены!</label>
           <input class="classic" name="submit" type="submit" style="background-color: #fff; color: #000" value="Отменить" onclick="setOnStorageLimitAction();">
       </div>
    </div>
</div>
</div>

<script>
    var periods_data; // На всякий случай весь объект периодов
    var periods, colors; // Отдельно наименования и цвета периодов
    var changeStatus = 0; // Эти поля будут хранить информацию об изменениях на случай закрытия вкладки без сохранений

    // Старт-пак вызовов
    $(document).ready(function () {
       selectPeriodsFromServer(); // Генерируем список периодов
       setOnStorageLimitAction();
       selectRole($('.edit_role').val()); // Отмечаем права для первой роли
       $('.choice_color').paletteColorPicker({  });
       $('.edit_for_select').val($(this).find('option:first').text()); // Прописываем имя поля селект в text поля
    });

    // Событие на закрытие вкладки/окна
    window.onbeforeunload = function (evt) {
      if (changeStatus > 0) {
         var message = "Введённые вами данные на странице не были сохранены. Продожить переход?";
      	evt = window.event;
      	evt.returnValue = message;
      	return message;
      }
   }

    // Заполнение списка периодов данными из таблиц
    function selectPeriodsFromServer() {
       $.ajax({
          type: 'POST',
          url: '/А/backend.php',
          data: {
             select_period: ''
          },
          success: function(req) {
             periods_data = JSON.parse(req);
             periods = JSON.parse(periods_data['period']);
             colors = JSON.parse(periods_data['color']);
             drawPeriodsOnScreen();
          }
       });
    }

    // Выбор действия при достижение лимита сайта, подгрузка с базы
    function setOnStorageLimitAction() {
       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             storage_limit_action: '0'
          },
          success: function(req) {
             $('.on_limit_action').find('#' + req).attr('checked', 'checked');
          }
       });
    }

    // Непосредственно отрисовка списка периодов
    function drawPeriodsOnScreen() {
       var appendHTML = '';

       for (var i = 0; i < periods.length; i++) {
          appendHTML += '<div class="period" id="' + i + '" style="background-color: ' + colors[i]['unit'] + ';"><label for="delete_period" style="color: #191919;">' + periods[i]['unit'] + '</label><input type="submit" class="delete_period" id="' + i + '" name="delete_period" value="x" onclick="deleteSelectedPeriod( this );" style="background-color: rgb(0, 0, 0, 0); margin: 0 3px 0 3px;min-width: 20px; width: 20px;"></div>';
       }

       $('.list_current_periods').html(appendHTML);
    }

    // Создания интерфейса для выбора цвета периода
    function showClicker() {
       $('.choice_color').paletteColorPicker({  });
    }

    // Если при регистрации тип пользователя указан как водитель
    // отображает поля для регистрации в приложение iWaterLogistic
    function driverChecker() {
      if ($(".selected_role").val() == 3) {
       $("#add_m_login").html('<input class="newUserLoginL" name="app_login" type="text" placeholder="Логин в приложение">');
       $("#add_m_login_title").html('М.Логин');
       $("#add_m_pass").html('<input class="newUserPasswordL" name="app_pass" type="password" placeholder="Пароль в приложение">');
       $("#add_m_pass_title").html('М.Пароль');
     } else {
       $("#add_m_login").html('');
       $("#add_m_login_title").html('');
       $("#add_m_pass").html('');
       $("#add_m_pass_title").html('');
     }
    }

    // Добавлеяем введённые данные в информацию о всех периодах и кидаем json'ом на сервер
    function addNewPeriod() {
       var selectedName = $('.selected_period').val();
       var selectedColor = $('.choice_color').val();

       periods.push({ 'unit' : selectedName });
       colors.push({ 'unit' : selectedColor });

       $('.selected_period').val('');
       $('.choice_color').val('');

       drawPeriodsOnScreen();

       // Изменения сохранены и не стоит предупреждать при переходе/закрытие
       changeStatus--;
    }

    // Удаление выбранного периода
    function deleteSelectedPeriod(ev) {
       var numberForDelete = $(ev).attr('id');

       periods.splice(numberForDelete, 1);
       colors.splice(numberForDelete, 1);

       drawPeriodsOnScreen();

       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             change_period: JSON.stringify(periods),
             change_color: JSON.stringify(colors)
          },
          success: function() {
             $('.selected_period').val('');
             $('.choice_color').val('');
             drawPeriodsOnScreen();
          }
       });
    }

    // Отправка данных о периоде на сервер
    function savePeriods() {
       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             change_period: JSON.stringify(periods),
             change_color: JSON.stringify(colors)
          },
          success: function() {
             selectPeriodsFromServer();
             $('.selected_period').val('');
             $('.choice_color').val('');

             $('.save_period_label').stop().animate({opacity:'1.0'}, 600);
             setTimeout(function() { $('.save_period_label').stop().animate({opacity:'0.0'}, 900); }, 2000);

             // Изменения сохранены и не стоит предупреждать при переходе/закрытие
             changeStatus--;
           }
       });
    }

    // Сохраняем дествие, которое происходит, если товара на складе не хватает
    function saveOnLimitAction() {
       var limitAction = $('.on_limit_action').children().find('input:checked').val();

       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             save_limit_action: limitAction,
          },
          success: function() {
             window.location.reload();
          }
       });
    }

    // Отмена изменения периодов
    function cancelPeriods() {
      selectPeriodsFromServer();
      $('.selected_period').val('');
      $('.choice_color').val('');

      // Изменения сохранены и не стоит предупреждать при переходе/закрытие
      changeStatus--;
   }

    // Отправка данных клиента для регистрации на сервер
    function createNewUser() {
       var name = $('.newUserName').val();
       var phone = $('.newUserPhone').val();
       var login = $('.newUserLogin').val();
       var password = $('.newUserPassword').val();
       var role = $('.selected_role').val();
       var logLogin, logPassword; // Данные от приложения iWaterLogistic
       if ($(".selected_role").val() == 3) {
          logLogin = $('.newUserLoginL').val();
          logPassword = $('.newUserPasswordL').val();

          $.ajax({
             type: 'POST',
             url: "/iwaterTest/backend.php",
             data: {
                add_user: "",
                name: name,
                phone: phone,
                login: login,
                password: password,
                role: role,
                app_login: logLogin,
                app_pass: logPassword
             },
             success: function(req) {
                $('.save_user_label').stop().animate({opacity:'1.0'}, 600);
                setTimeout(function() { $('.save_user_label').stop().animate({opacity:'0.0'}, 900); }, 2000);
             }
          });
       } else {
          $.ajax({
             type: 'POST',
             url: "/iwaterTest/backend.php",
             data: {
		add_user: "",
                name: name,
                phone: phone,
                login: login,
                password: password,
                role: role
             },
             success: function(req) {
                $('.save_user_label').stop().animate({opacity:'1.0'}, 600);
                setTimeout(function() { $('.save_user_label').stop().animate({opacity:'0.0'}, 900); }, 2000);
             }
          });
       }

       // Изменения сохранены и не стоит предупреждать при переходе/закрытие
       changeStatus--;
    }

    // Добавление ролей
    function addRole() {
       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             add_role: '',
             name: 'Новая роль',
             perms: '[]'
          },
          success: function() {
             window.location = "/iwaterTest/admin/admin_panel/";
          }
       });
    }

    // Сохранение изменений для роли
    function saveRole() {
      var elements = $('.perms_list').children();
      var roleName = $('.edit_for_select').val();
      var result = [];

      $.each(elements, function(i, item, arr) {
         result.push($(item).children().find('input:checked').val());
      });

      console.log(JSON.stringify(result));

      $.ajax({
         type: 'POST',
         url: '/iwaterTest/backend.php',
         data: {
            update_role: $('.edit_role').val(),
            role_name: roleName,
            perms_list: JSON.stringify(result)
         },
         success: function() {
            selectRole($('.edit_role').val()); // Помимо анимации, тут перезапрашиваем права, чтобы не перезагружать страницу целиком
            $('.edit_role_label').stop().animate({opacity:'1.0'}, 600);
            setTimeout(function() { $('.edit_role_label').stop().animate({opacity:'0.0'}, 900); }, 2000);
         }
      });

      // Изменения сохранены и не стоит предупреждать при переходе/закрытие
      changeStatus--;
    }

    // Отмена изменений, по сути, просто перезагрузка данных
    function cancelRolesEdit() {
       selectRole($('.edit_role').val());

       // Изменения сохранены и не стоит предупреждать при переходе/закрытие
       changeStatus--;
    }

    // Удаление выбранной роли
    function deleteRole() {
       var numberRole = $('.edit_role').val();

       $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             delete_role: numberRole
          },
          success: function() {
             $('.edit_role_label').stop().animate({opacity:'1.0'}, 600);
             setTimeout(function() { $('.edit_role_label').stop().animate({opacity:'0.0'}, 900); }, 2000);
          }
       });
    }

    // Получение данных о роли с сервера
    function selectRole(num) {


      // Проставляем данные с сервера в чекбоксы
      $.ajax({
          type: 'POST',
          url: '/iwaterTest/backend.php',
          data: {
             select_role: num
          },
          success: function(req) {
             var perms = JSON.parse(req);
             var html = '';

             for (var i = 0; i < perms.length; i++) {
                html += '<div class="perms_choice"><div class="section_name" id="' + perms[i][0] + '">' + perms[i][1] + '</div>';
                html += '<label class="checkbox-conteiner"><input type="radio" ' + ((perms[i][2] == '0') ? 'checked' : '') + ' name="section_level_' + i + '" value="0" id="' + i + '" class="checkbox"><span class="checkbox-visual"></span><span class="checkbox-text">Запретить доступ</span></label>';
                html += '<label class="checkbox-conteiner"><input type="radio" ' + ((perms[i][2] == '1') ? 'checked' : '') + ' name="section_level_' + i + '" value="1" id="' + i + '" class="checkbox"><span class="checkbox-visual"></span><span class="checkbox-text">Просмотр</span></label>';
                html += '<label class="checkbox-conteiner"><input type="radio" ' + ((perms[i][2] == '2') ? 'checked' : '') + ' name="section_level_' + i + '" value="2" id="' + i + '" class="checkbox"><span class="checkbox-visual"></span><span class="checkbox-text">Добавление</span></label>';
                html += '<label class="checkbox-conteiner"><input type="radio" ' + ((perms[i][2] == '3') ? 'checked' : '') + ' name="section_level_' + i + '" value="3" id="' + i + '" class="checkbox"><span class="checkbox-visual"></span><span class="checkbox-text">Редактирование</span></label>';
                html += '<label class="checkbox-conteiner"><input type="radio" ' + ((perms[i][2] == '4') ? 'checked' : '') + ' name="section_level_' + i + '" value="4" id="' + i + '" class="checkbox"><span class="checkbox-visual"></span><span class="checkbox-text">Удаление</span></label>';
                html += '</div>';
             }

             $('.perms_list').html(html);
          }
       });
    }


</script>

<!-- СТИЛИ ДЛЯ ОБЩИХ И ЛОКАЛЬНЫХ ЭЛЕМЕНТОВ -->
<style media="screen">
   /* ОБЩИЕ ЭЛЕМЕНТЫ УПРАВЛЕНИЯ */
   input {
      width: 185px;
      height: 25px;
      border-radius: 5px;
      border-color: #e3e9f1;
      border-style: solid;
   }
   input[type=submit] {
      width: 100px;
      height: 30px;
      background-color: #0157a4;
      color: #fff;
      border-radius: 8px;
      cursor: pointer;
   }
   select {
      width: 185px;
      height: 35px;
      border-width: 2px;
      border-radius: 5px;
      border-color: #e3e9f1;
      border-style: solid;
   }
   .main_form label {
      width: 200px !important;
   }

   /* ЛОКАЛЬНЫЕ ЭЛЕМЕНТЫ УПРАВЛЕНИЯ */

   .classic {
      float: right;
   }

   .on_area_button {
      background-color: #fff;
      color: #0157a4;
      border: none;
   }

   .add_client {
      max-width: 607px;
      padding-left: 20px;
   }
   .add_client_table {
      width: 605px;
      height: 175px;
      padding: 10px 45px 10px 25px;
      border-radius: 10px;
      font-size: 15px;
      float: left;
      background-color: #fff;
      color: #7a7a7a;
   }

   .add_dev_email {
      width: 20px;
      height: 20px;
      float: left;
      margin: 10px 0 0 10px;
      font-size: 20px;
      text-align: center;
      cursor: pointer;
      background-color: #fff;
      color: #0157a4;
   }

   .add_period_named {
      width: 120px;
      height: 20px;
      margin: 10px 0 0 0;
      font-size: 15px;
      display: block;
      text-align: center;
      cursor: pointer;
      background-color: #fff;
      color: #0157a4;
   }

   .list_current_periods {
      margin-top: 15px;
      display: flex;
   }

   .category_label {
      float: left;
      font-size: 21px;
      padding: 40px 0 20px 25px;
   }

   .periods {
      display: flex;
   }

   .period_list_cont {
      float: left;
      display: inherit;
      padding-left: 20px;
   }
   .period_list {
      display: grid;
      width: 670px;
      height: 180px;
      padding: 10px 45px 10px 25px;
      border-radius: 10px;
      background-color: #fff;
      color: #7a7a7a;
   }

   .save_period_label {
      float: right;
      display: block;
      opacity: 0.0;
      font-size: 16px;
      color: #0157a4;
   }

   .save_user_label {
      float: right;
      display: block;
      opacity: 0.0;
      font-size: 16px;
      color: #0157a4;
   }
   .edit_role_label {
      float: right;
      display: block;
      opacity: 0.0;
      font-size: 16px;
      color: #0157a4;
   }

   .add_role_b {
      float: right;
   }

   .save_period {
      float: right;
   }

   .add_role_cont {
      float: left;
      max-width: 890px;
      margin: 0 0 0 15px;
   }
   .storage_limit {
      float: left;
      max-width: 890px;
      margin: 0 0 0 15px;
   }
   .add_role {
      width: 800px;
      height: auto;
      padding: 10px 45px 10px 25px;
      border-radius: 10px;
      background-color: #fff;
      color: #7a7a7a;
      display: flow-root;
   }
   .on_limit_action {
      width: 410px;
      height: 95px;
      padding: 10px 45px 10px 25px;
      /* margin: 120px 0px 0px 20px; */
      border-radius: 10px;
      background-color: #fff;
      color: #7a7a7a;
   }

   .edit_role_links {
      float: right;
      padding: 15px 0 0 15px;
   }

   .select-role {
      width: 1000px;
   }

   .period {
      max-width: 140px;
      max-height: 25px;
      margin: 0 5px 0 0;
      display: flex;
      border: 1px solid #c6c6c6;
      border-radius: 20px;
   }
   .period label {
      padding: 0px 0 0 7px;
   }
   .period input[type=submit] {
      width: 30px;
      height: 25px;
      background-color: #fff;
      color: #191919;
      border: none;
      cursor: pointer;
      margin: 0;
   }

   /* КАСТОМНЫЙ ЧЕКБОКС */

   .checkbox-conteiner {
     width: 300px;
     display: block;
     cursor: pointer;
     float:left;
   }
   .checkbox {
     display: none;
   }
   .checkbox-text {
      border: none;
      line-height: 20px;
      background-color: #fff;
   }
   .checkbox-visual {
     position: relative;
     display: inline-block;
     vertical-align: top;
     margin-right: 12px;
     padding: 0px;
     width: 15px;
     height: 15px;
     border-radius: 12px;
     border: none;
     background-color: #9c9c9c;
   }
   .checkbox-visual:before {
     content: '';
     display: none;
     position: absolute;
     top: 50%;
     left: 50%;
     margin: -5px 0 0 -6px;
     height: 4px;
     width: 8px;
     border-width: 0 0 4px 4px;
     -webkit-transform: rotate(-45deg);
     -moz-transform: rotate(-45deg);
     -ms-transform: rotate(-45deg);
     -o-transform: rotate(-45deg);
     transform: rotate(-45deg);
   }
   .checkbox:checked ~ .checkbox-visual {
     background-color: : #015aaa;
   }
   .checkbox:checked ~ .checkbox-visual:before {
     display: block;
   }
   .checkbox:checked ~ .checkbox-visual {
      background-color: #015aaa;
   }
   .edit_for_select {
      height: 24px;
      margin-left: -215px;
      padding-top: 1px;
      z-index: 999;
      border-radius: 5px 0 0 5px;
      border: 2px solid #e3e9f1;
      border-right: 0px;
   }

   .edit_role:focus, .edit_for_select:focus {
      outline:none;
   }

   .palette-color-picker-button {
      width: 25px;
      height: 25px;
      background: rgb(114, 114, 114);
      margin: 8px 0px 0px 10px;
      border-radius: 13px;
   }
   .palette-color-picker-bubble {
      display: inline-flex;
      padding-top: 34px;
   }
   .swatch {
      width: 10px !important;
      height: 10px !important;
      padding: 0;
      margin: 0;
   }
   .section_name {
      display: block;
      width: 245px;
      height: 30px;
      text-align: center;
      font-size: large;
   }
   .perms_choice {
      display: inline-table;
      width: 245px;
      margin: 10px;
      height: 201px;
   }
</style>
