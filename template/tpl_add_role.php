<div class="main">
    <div class="add_role">
        <form method="post" action="/iwaterTest/backend.php">
            <div><input name="name" type="text" placeholder="Имя">
                <div>
                    <input name="add_role" type="hidden">
                    <?php echo checkbox_perms(); ?>
                    <input name="submit" type="submit" value="Добавить">
        </form>
    </div>
    <div>
