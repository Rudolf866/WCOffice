<div class="main">
    <div class="main_form">
        <form method="post" action="/iwaterTest/backend.php">
            <label for="name">Имя</label><input name="name" type="text" placeholder="Имя">
            <label for="phone">Телефон</label> <input name="phone" type="phone" placeholder="Телефон">
            <label for="login">Логин</label><input name="login" type="text" placeholder="Логин">
            <label for="password">Пароль</label><input name="password" type="password" placeholder="Пароль">
            <input name="add_user" type="hidden">
            <div><label for="role">Роль</label>

            <select name="role" class="selected_role" onchange="driverChecker();">
                <?php echo select_role(); ?>
            </select>
            </div>

            <div class="for_driver"></div>
            <script>
            function driverChecker() {
              if ($(".selected_role").value = '3') {
                $(".for_driver").html('<label for="app_login">Логин</label><input name="app_login" type="text" placeholder="Логин в приложение"><label for="app_pass">Пароль</label><input name="app_pass" type="password" placeholder="Пароль в приложение">');
              }
            }
            </script>
            <input class="classic" name="submit" type="submit" value="Добавить">
        </form>
    </div>
    <div>
