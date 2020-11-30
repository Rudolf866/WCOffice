<div class="main">
   <form id="iputUnit" style="width:850px; height: 450px; margin:0 0 0 25px;display: inline-block;">
     <div class="name_title">
       <div class="name_position">Добавить товар</div>
     </div>
     <fieldset>
       <table style="width: 25%; display: inline-block; margin: 0 5px;">
         <tbody style="width: 100%; display: inline-block;">
           <tr>
             <td> Наименование:</td>
           </tr>
           <tr style="width: 100%; display: inline-block;">
             <td style="width: 100%; display: inline-block;"><input type="text" maxlength=86 id="unitName" style="width: 100%;" placeholder="Наименование товара" autocomplete="off" oninput="maxLengthName();"/> <span class="leng_alert" style="color:red; display: none;">Максимальная длина 86 символов</span></td>
           </tr>
           <tr>
               <td> Категория:</td>
           </tr>
           <tr style="width: 100%; display: inline-block;">
               <td><select id="select_category" style="min-width: 214px;"></select></td>
           </tr>
           <tr>
               <td> Единица измерения:</td>
            </tr>
            <tr style="width: 100%; display: inline-block;">
               <td><select class="select_measure" name="" style="min-width: 214px;">
                 <option>литр</option>
                 <option>штука</option>
                 <option>коробка</option>
                 <option>комплект</option>
               </select> </td>
           </tr>
         <tr>
           <td> Скидка в процентах:</td>
         </tr>
         <tr style="width: 100%; display: inline-block;">
           <td style="width: 100%; display: inline-block;"><input type="text" id="unitProcent" value="0" style="width: 100%;" onclick=' if ($("#unitProcent").val() == "0") { $("#unitProcent").val(""); }' on /></td>
         </tr>
         </tr>
             <td> Логотип:</td>
             <td>
                <label for="unitLogo" style="width: 110px; height: 17px; background-color: #015aaa; margin: 0 0 0 45px; display: block; color: #fff; border-radius: 10px; text-align: center; cursor: pointer;">Выбрать</label>
                <input type="file" id="unitLogo" style="display: none;"/>
             </td>
         </tr>
        </tbody>
       </table>

     <table style="width: 69%; float: right; margin: 0 5px;">
         <tbody>
             <tr>
                 <td> Описание:</td>
             </tr>
             <tr>
                 <td><textarea id="unitSubscribe" class="tinyEditor" style="width: 100%; height: 80px;" placeholder="Описание товара"></textarea></td>
             </tr>
         </tbody>
     </table>
     <table style="width: 100%; margin: 0 5px;">
         <tbody>
             <tr>
                 <td>Изображения галлереи</td>
             </tr>
             <tr>
                <td>
                   <div class="images_upload">
                      <button type="button" name="button">Выбрать</button>
                      <div>Файл не выбран!</div>
                      <input type="file" multiple="multiple" accept=".txt,image/*">
                      <div class="images_list"></div>
                   </div>
                </td>
         </tbody>
     </table>
     <table style="width: 37%; display: inline-block; margin: 0 5px;">
         <tbody style="width: 100%; display: inline-block;">
            <tr style="width: 100%; display: inline-block;">
             <td style="width: 100%; display: inline-block;"> <!-- <input type="text" id="unitPrice" style="width: 100%;" placeholder="(Граница цены):(Цена);" oninput="priceShow();" autocomplete="off" hidden/> -->
                <!-- <table id="priceTest" style="text-align: center; margin: 3px;">
                   <tr class="margin_price">
                      <td>
                        Цена от
                      </td>
                      <td>
                        <input type="text" name="" value="">
                      </td>
                   </tr>
                   <tr class="value_price">
                      <td>
                        Цена за шт.
                      </td>
                      <td>
                        <input type="text" name="" value="">
                      </td>
                   </tr>
                   <tr class="delete_price">
                      <td>

                      </td>
                      <td>
                        <input type="button" name="" value="x" style="background-color: #015aaa; color: #fff; width: 17px; border: none; border-radius: 8px; padding: 0 0 2px 0; cursor: pointer;">
                      </td>
                   </tr>
                </table> -->
                <!-- <input type="button" name="" value="+" style="background-color: #015aaa; color: #fff; width: 17px; border: none; border-radius: 8px; padding: 0 0 2px 0; cursor: pointer;"> -->
                <input type="text" id="unitPrice" name="price" style="width: 100%;" placeholder="(Граница цены):(Цена);" oninput="priceShow();" autocomplete="off"/>
                 <table id="priceTest" border="1" style="text-align: center; margin: 3px;"></table>
             </td>
            </tr>
         </tbody>
     </table>
     </fieldset>
     <input class="search_button" type="button" id="savedata" value="Сохранить" onclick="saveUnit();" style="float: right; margin-bottom: 30px;" />
     <input class="reset_button" type="button" id="savedata" value="Отменить" onclick="" style="float: right; margin-bottom: 30px;" />
   </form>
</div>

<script src="https://cloud.tinymce.com/stable/tinymce.min.js?apiKey=broneasi6jcr06k9rwltme95vluo4jtz1r5vvdot4pdkti7a"></script>
<script type="text/javascript"\>

   tinyMCE.init ({
      selector : "textarea#unitSubscribe",
      menubar: false,
      plugins: [
       'advlist autolink lists link image charmap print preview anchor textcolor',
     ],
     toolbar: 'fontselect fontsizeselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify',
     content_css: [
       '//fonts.googleapis.com/css?family=Lato:300,300i,400,400i',
       '//www.tinymce.com/css/codepen.min.css']
   });
   var listEdit = " ";

   /**
    * Построение галлереи изображений
   */
   $.ajax({
      type: 'POST',
      url: '/iwaterTest/backend.php',
      data: {
         gallery_list: <?php echo $_GET['id']; ?>
      },
      success: function(res) {
         var list = JSON.parse(res);
         for (image in list) {
            $('.images_list').append('<div class="brick small"><img src="/iwater_api/images/<?php echo $_GET['id']; ?>/' + list[image] + '"><a class="delete" href="#" onclick="deleteImage(' + list[image] + ')">&times;</a></div>');
         }

         // Строим по дивам галлерею
         $('.images_list').gridly({
            base: 60, // px
            gutter: 20, // px
            columns: 12
         });
      }
   });

   $.ajax({
      type: "POST",
      url: "/iwaterTest/backend.php?category",
      data: {
          category: "sad"
      },
      datatype: "json",
      success: function (data) {
          if (data.length > 1) {
               for(var k in data) {
                   var v = data[k];
                   cat.push(k, v);
               }
               cat = cat.filter((e,i)=>(i%2));
               cat = JSON.stringify(cat);
               cat = JSON.parse(cat.toString());

               ajaxCallBack(cat[0][1] + ": " + cat[0][0]);

               for (var i = 0; i < cat.length; i++) {
                   ajaxCallBack("; " + cat[i][1] + ": " + cat[i][0], cat[i][1], cat[i][0]);
               }
          }
        }
    });

    $.ajax({
      type: 'POST',
      url: '/iwaterTest/backend.php',
      data: {
          unit_info: <?php echo $_GET['id']; ?>
      },
      success: function(res) {
          var data = JSON.parse(res);

          $('#unitName').val(data.name);
          $('#unitPrice').val(data.price);
          $('#unitProcent').val(data.discount);
          $('#unitSubscribe').html(data.about);
          $('#unitLinks').val(data.gallery);
          $('#select_category').val(data.category);
          $('#select_measure').val(data.measure);
      }
    });

     $("#unitLogo").keyup( function (e) {
      if (e.keyCode == 13) {
         addNewUnit();
      }
     });

     var listEdit = " ";
     var cat = [];

     function ajaxCallBack(edit, x, y) {
         listEdit += edit;
         if (typeof y !== 'undefined') {
           $("#select_category").append('<option value="' + x + '">' + y + '</option>');
         }
     }

     // Сохранить информацию о товаре
     function saveUnit() {
      var aboutUn = tinyMCE.activeEditor.getContent();
      var nameUn = $("#unitName").val();
      // var aboutUn = $("#unitSubscribe").val();
      var priceUn = $("#unitPrice").val();
      var discountUn = $("unitProcent").val();
      var categoryUn = $("#select_category").val();
      var galleryUn = $("#unitLinks").val();
      var logoUn = $("#unitLogo").val();
      if (nameUn != '') {
        $.ajax({
          type: "POST",
          url: "/iwaterTest/backend.php?units",
          data: {
            oper: "edit",
            id: "<?php echo $_GET['id']; ?>",
            name: nameUn,
            price: priceUn,
            discount: discountUn,
            category: categoryUn,
            gallery: galleryUn,
            logo: logoUn,
            about: aboutUn
          },
          success: function () {
             window.location = "/iwaterTest/admin/list_unit";
          }
        });
      } else {
        alert('Не введены основные данные!');
      }
   }

     // Удаление изображения из галлереи
     function deleteImage(id) {
        $.ajax({
           type: 'POST',
           url: '/iwaterTest/backend.php',
           data: {
             delete_gallery: <?php echo $_GET['id']; ?>,
             image: id
          },
          success: function() {
             console.log('deleted');
          }
        });
     }

     //Функции отрисовки цены в виде новой таблицы
     function priceShow() {
       var first = $("#unitPrice").val().split(';');
       var string = '<tr>';

       for (var i = 0; i < first.length; i++) {
         if (first[i].split(':')[0] != '') {
           string += '<td>от ';
           string += first[i].split(':')[0];
           string += '</td>';
         }
       }

       string += '</tr> <tr>';

       for (var i = 0; i < first.length; i++) {
         if (typeof first[i].split(':')[1] !== 'undefined') {
           string += '<td>';
           string += first[i].split(':')[1];
           string += 'р.</td>';
         }
       }

       string += '</tr>';
       $("#priceTest").html('');
       $("#priceTest").append(string);
     }


 function maxLengthName() {
   if ($('#unitName').val().length == 86) {
     $('.leng_alert').css("display", "block");
   } else {
     $('.leng_alert').css("display", "none");
   }
 }

 // Кастомное поле выгрузки файлов на хост
 var wrapper = $( ".images_upload" ),
     inp = wrapper.find( "input" ),
     btn = wrapper.find( "button" ),
     lbl = wrapper.find( "div" );
   btn.focus(function(){
        inp.focus()
   });
   // Crutches for the :focus style:
   inp.focus(function(){
        wrapper.addClass( "focus" );
   }).blur(function(){
        wrapper.removeClass( "focus" );
   });

   var file_api = ( window.File && window.FileReader && window.FileList && window.Blob ) ? true : false;

    inp.change(function(){
        var file_name;
        if( file_api && inp[ 0 ].files[ 0 ] )
            file_name = inp[ 0 ].files[ 0 ].name;
        else
            file_name = inp.val().replace( "C:\\fakepath\\", '' );

        if( ! file_name.length )
            return;

        if( lbl.is( ":visible" ) ){
            lbl.text( file_name );
            btn.text( "Выбрать" );
        }else
            btn.text( file_name );
    }).change();
 </script>

<style media="screen">
  fieldset {
      border: 0;
      background-color: #fff;
      border-radius: 8px;
      margin-top: 30px;
  }

  tbody {
      width: 100% !important;
  }

  fieldset>input {
      width: 100% !important;
  }
  .images_upload {
     width: 800px;
     height: 230px;
     overflow: hidden;
  }
  .images_upload > button {
     width: 8em;
     height: 17px;
     margin: 2px 20px 0 0;
     float: left;
     border: none;
     border-radius: 15px;
     color: #fff;
     background-color: #015aaa;
  }
  .images_upload > div {
     padding: 4px 0 0 9px;
  }
  .images_upload input[type=file] {
     position: relative;
     left: 0;
     top: -30px;
     width: 300px;
     letter-spacing: 10em;     /* IE 9 fix */
     -ms-transform: scale(20); /* IE 9 fix */
     opacity: 0;
     cursor: pointer
  }
  .images_list {
    width: 800px !important;
    height: auto !important;
  }
  .brick.small {
    position: inherit !important;
    display: contents;
    max-width: 140px;
    max-height: 140px;
  }
  .brick.small img {
    max-width: 130px;
    max-height: 140px;
  }
  #priceTest input {
     width: 45px;
  }
</style>
