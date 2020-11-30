<img src="../../css/image/loading-gallery.gif" class="loading" alt="" style="width: 141px; display: none;"/>
<form id="inputUnit" name="inputUnit" enctype="multipart/form-data" action="/iwaterTest/backend.php?unit" style="width:850px; height: 450px; margin:0 0 0 25px;display: inline-block;">
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
          <td style="width: 100%; display: inline-block;"><input type="text" maxlength=86 id="unitName" name="name" style="width: 100%;" placeholder="Наименование товара" autocomplete="off" oninput="maxLengthName();"/> <span class="leng_alert" style="color:red; display: none;">Максимальная длина 86 символов</span></td>
        </tr>
        <tr>
          <td> Цена:</td>
        </tr>
        <tr style="width: 100%; display: inline-block;">
          <td style="width: 100%; display: inline-block;"><input type="text" id="unitPrice" name="price" style="width: 100%;" placeholder="(Граница цены):(Цена);" oninput="priceShow();" autocomplete="off"/>
            <table id="priceTest" border="1" style="text-align: center; margin: 3px;"></table> <!-- Здесь будет отображаться цена в виде таблицы -->
          </td>
        </tr>
      <tr>
        <td> Скидка в процентах:</td>
      </tr>
      <tr style="width: 100%; display: inline-block;">
        <td style="width: 100%; display: inline-block;"><input type="text" id="unitProcent" name="procent" value="0" style="width: 100%;" onclick=' if ($("#unitProcent").val() == "0") { $("#unitProcent").val(""); }' on /></td>
      </tr>
     </tbody>
    </table>

  <table style="width: 69%; float: right; margin: 0 5px;">
      <tbody>
          <tr>
              <td> Описание:</td>
          </tr>
          <tr>
              <td><textarea id="unitSubscribe" class="tinyEditor" style="width: 100%; height: 80px;" placeholder="Описание товара" name="about"></textarea></td>
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
                  <input type="file" multiple="multiple" name="gallery[]" accept="image/*">
              </div>
            </td>
      </tbody>
  </table>
  <table style="width: 37%; display: inline-block; margin: 0 5px;">
      <tbody style="width: 100%; display: inline-block;">
          <tr>
              <td> Категория:</td>
              <td> Единица измерения:</td>
          </tr>
          <tr>
              <td><select id="select_category" name="category" style="width: 100%;"></select></td>
              <td><select class="select_measure" name="measure">
<!--                <option>литр</option>-->
<!--                <option>штука</option>-->
<!--                <option>коробка</option>-->
<!--                <option>комплект</option>-->
<!--                 <option>кг</option>-->
                      <?php echo get_dimension(); ?>
              </select> </td>
          </tr>
      </tbody>
  </table>
  <input type="text" name="oper" value="add" hidden>
  <table style="width: 60%; float: right; margin: 0 5px;">
      <tbody>
          </tr>
              <td> Логотип:</td>
          </tr>
          <tr>
              <td>
                 <label for="unitLogo" style="width: 210px; height: 20px; background-color: #015aaa; display: block; color: #fff; border-radius: 10px; text-align: center; cursor: pointer;">Загрузить изображение</label>
                 <input type="file" id="unitLogo" name="logo" style="display: none;"/>
              </td>
          </tr>
      </tbody>
  </table>

  </fieldset>
  <input class="search_button" type="button" id="savedata" value="Добавить" onclick="addNewUnit();" style="float: right;     margin-bottom: 30px;" />
  <input class="reset_button" type="button" id="savedata" value="Отменить" onclick="addNewUnit();" style="float: right;     margin-bottom: 30px;" />
</form>

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

   function addNewUnit() {
     //var aboutUn = tinyMCE.activeEditor.getContent();
	 tinyMCE.triggerSave();// сохранить данные из редактора в бд
     //var aboutUn = tinyMCE.activeEditor.getContent({format : 'text'});
     var aboutUn = tinyMCE.activeEditor.selection.getContent({format : 'text'});
     var nameUn = $("#unitName").val();
     var priceUn = $("#unitPrice").val();
     var discountUn = $("unitProcent").val();
     var categoryUn = $("#select_category").val();
     var galleryUn = $("#unitLinks").val();
     var logoUn = $("#unitLogo").val();

     console.log(logoUn);

     var formData = new FormData(document.forms.inputUnit);//$('#inputUnit')[0]);
     formData.append('key1', 'value1');

     for (var pair of formData.entries()) {
        console.log(pair);
     }
     $('.loading').show();

     $.ajax({
        type: "POST",
        url: "/iwaterTest/backend.php?units",
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
           $('.loading').hide();
           window.location = "/iwaterTest/admin/list_unit/";
        }
     });

     // if (nameUn != '' && priceUn != '' && logoUn != '') {
     //   $.ajax({
     //     type: "POST",
     //     url: "/iwaterTest/backend.php?units",
     //     contentType: false,
     //     processData: false,
     //     data: {
     //       oper: "add",
     //       id: "0",
     //       name: nameUn,
     //       price: priceUn,
     //       discount: discountUn,
     //       category: categoryUn,
     //       gallery: galleryUn,
     //       logo: logoUn
     //     },
     //     success: function () {
     //       $("#unitName").val('');
     //       $("#unitSubscribe").val('');
     //       $("#unitPrice").val('');
     //       $("unitProcent").val('0');
     //       $("#unitLinks").val('');
     //       $("#unitLogo").val('');
     //           tinyMCE.activeEditor.setContent('');
     //           $("#list").trigger("reloadGrid");
     //     }
     //   });
     // } else {
     //   alert('Не введены основные данные!');
     // }
   }

     var listEdit = " ";
     var cat = [];


     $("#unitLogo").keyup( function (e) {
      if (e.keyCode == 13) {
         addNewUnit();
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

     function ajaxCallBack(edit, x, y) {
         listEdit += edit;
         if (typeof y !== 'undefined') {
           $("#select_category").append('<option value="' + x + '">' + y + '</option>');
         }
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
     width: 300px;
     height: 24px;
     border-radius: 7px;
     overflow: hidden;
  }
  .images_upload > button {
     width: 8em;
     height: 20px;
     margin: 2px;
     float: right;
     border: none;
     border-radius: 10px;
     color: #fff;
     background-color: #015aaa;
  }
  .images_upload > div {
     padding: 4px 0 0 1px;
  }
  .images_upload input[type=file] {
     position: relative;
     left: 0;
     top: 0;
     width: 100%;
     height: 100%;
     transform: scale(20);
     letter-spacing: 10em;     /* IE 9 fix */
     -ms-transform: scale(20); /* IE 9 fix */
     opacity: 0;
     cursor: pointer
  }
</style>
