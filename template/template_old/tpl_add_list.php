<div class="main">
    <div class="main_form">
        <form id="add_list_form" method="post" action="/iwaterTest/backend.php">
            <input id="date_list" name="date" type="text" placeholder="Дата путевого (d/m/Y)">
            <select  class="classic" id="driver" name="driver">
                <option value="All">Все водители</option>
                <?php echo select_driver(); ?>
            </select>
            <input name="add_list" type="hidden">
            <input class="classic" name="submit" type="submit" value="Сформировать">
        </form>
    </div>

    <div>
        <script>
            $(function(){
                $('#driver option[value="' + 0 + '"]').text("Нераспределенные точки");
                $('#driver').val("All");
                $("#date, #date_list").datepicker();
                $("#date, #date_list").datepicker("option", "dateFormat", "dd/mm/yy");
                update_table();
            });

            function update_table() {
                var data = [];
                data[0] = $('#date').val();
                data = JSON.stringify(data);
                $.ajax({
                    type: "POST",
                    data: {list_lists: data},
                    url: "/iwaterTest/backend.php",
                    success: function (req) {
                        create_table(req);
                    }
                });

            }

            $(document).ready(function () {
                jQuery.validator.addMethod(
                    "australianDate",
                    function (value, element) {
                        return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
                    },
                    "Введите дату в формате dd/mm/yyyy.");
                $("#add_list_form").validate({
                    rules: {
                        date: {australianDate: true}
                    },
                    errorPlacement: function(error, element) {
                        error.insertAfter($("#add_list_form"));
                    }
                });
            });

            function create_table(req) {
                $('#list_lists tr').each(function () {
                    $(this).parent().remove()
                });
                var row = document.getElementById('list_lists').insertRow(-1);
                var cell = row.insertCell(-1).innerHTML = "Имя файла";

                var i = 0;
                var rows = req.getElementsByTagName('rows')[0];
                l = rows.getElementsByTagName('row').length;
                while (i < l) {
                    var request = rows.getElementsByTagName('row')[i];
                    var file = request.getElementsByTagName("cell")[0];

                    row = document.getElementById('list_lists').insertRow(-1);
                    cell = row.insertCell(-1).innerHTML = file.innerHTML;
                    i++;
                }

            }
        </script>
