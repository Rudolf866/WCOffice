<div class="main">
   <!-- ИСТОРИЯ ОПЕРАЦИЙ -->
   <div class="category_label">
      История операций
      <hr color="#e3e9f1" size="1px" width="850px">
   </div>
   <div class="clients_list_table">
      <table class="main_table" id="logs"></table>
      <div id="pagerLogs"></div>

      <script>

         $(document).ready(function() {
            drawTable(1);
         });

         // Получение данных о путевых листах из базы, сборка таблицы и пагинатора
         function drawTable(page) {
            $.ajax({
               type: 'POST',
               url: '/iwaterTest/backend.php?logs=<?php echo get_log_attr(); ?>',
               data: {
                  page: page
               },
               success: function(xml) {
                  var total_pages = $(xml).find('total').text();
                  var current_page = $(xml).find('page').text();

                  /** Собираем пагинатор */
                  var start_page = (current_page - 3 < 1) ? 1 : current_page - 3;
                  var finish_page = (current_page -1 + 4 > total_pages) ? total_pages : current_page - 1 + 4;
                  var left = (current_page - 1 < 1) ? 1 : current_page - 1;
                  var right = (current_page - 1 + 2 < 1) ? 1 : current_page - 1 + 2;
                  var paginator_string = '<input type="button" class="pagination" onclick="drawTable(' + left + ')" value="<">';

                  for (var i = start_page; i <= finish_page; i++) {
                     if (i == page) {
                        paginator_string += '<input type="button" class="pagination" onclick="drawTable(' + i + ')" value="' + i + '" style="color: #000;">';
                     } else {
                        paginator_string += '<input type="button" class="pagination" onclick="drawTable(' + i + ')" value="' + i + '">';
                     }
                  }
                  paginator_string += '<input type="button" class="pagination" onclick="drawTable(' + right + ')" value=">">';

                  $('#pagerLogs').html(paginator_string); // Собрали постраничную навигацию

                  var table_string = '';
                  table_string += '<tr style="background-color: #fff0; height: 30px;"><th>Время</th> <th>Администратор</th> <th>Сфера операции</th> <th>Действие</th> <th>Таблица</th> <th>Данные</th></tr>';

                  $(xml).find("row").each(function () {
                     var row = $(this).find('cell');

                     table_string += '<tr>';
                     table_string += '<td>' + $(row[0]).text() + '</td>';
                     table_string += '<td>' + $(row[1]).text() + '</td>';
                     table_string += '<td>' + $(row[2]).text() + '</td>';
                     table_string += '<td>' + $(row[3]).text() + '</td>';
                     table_string += '<td>' + $(row[4]).text() + '</td>';
                     table_string += '<td>' + $(row[5]).text() + '</td>';
                     table_string += '</tr>';
                  });

                  $('#logs').html(table_string);
               }
            });
         }

      </script>
   </div>
</div>

<style media="screen">
   .main {
      display: table;
   }

   .category_label {
      float: left;
      font-size: 21px;
      padding: 20px 0 20px 25px;
   }
   .pagination {
      width: auto;
      height: auto;
      background-color: #fff;
      border: 1px solid #e2e6ee;
      border-radius: 0px;
      color: gray;
      cursor: pointer;
      padding: 9px 10px 20px 10px;
   }
   #pagerLogs {
      float: right;
   }
   .main_table {
      text-align: center;
   }
</style>
