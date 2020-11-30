<div class="main">
    <div class="user_list_table">
        <form method="post" action="/iwaterTest/backend.php">
            <?php if (check_perms('edit_users')) { ?>
                <input name="submit" type="submit" value="Сохранить изменения">
            <?php } ?>
            <table id="list_users" class="main_table">
                <?php echo user_list_table(); ?>
            </table>
            <input name="list_users" type="hidden">
        </form>
        <div>
            <div>

                <?php
                ?>
