<?php
   $access_level = check_perms('list_unit');
?>

        <style type="text/css">
            #gbox_categ {
                float: left;
            }

            /*локальные стили*/

            #list_id {
                /* width: 164px !important; */
            }
            #list_gl_id,
            #list_name,
            #list_about,
            #list_price,
            #list_discount,
            #list_gallery,
            #list_category,
            #list_logo
             {
                /* width: 140px !important; */
            }

            .s-ico {
                display: none !important;
            }
            #list td{
                /* width: 140px !important; */
            }

            #pager {
                display: none;
            }

            #gview_categ {
                display: none;
            }

            #editor {
                display: none;
            }
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

            .mce-flow-layout {
                white-space: nowrap !important;
            }

            #unitLinks {
                border-radius: 5px;
                border: 2px solid #e3e9f1;
            }

            .ui-jqgrid .ui-jqgrid-bdiv {
               overflow-x: hidden;
            }

            .ui-jqgrid .ui-jqgrid-view,
            .ui-jqgrid .ui-widget .ui-widget-content .ui-corner-all {
                /* width: 1020px !important; */
            }

            .ui-state-default, .ui-widget-content .ui-state-default {
                /* width: auto !important; */
            }
            .list_conteiner::-webkit-scrollbar {
                width: 12px;
                background-color: #F5F5F5;
            }

            .list_conteiner::-webkit-scrollbar-thumb {
                border-radius: 10px;
                background-color: #65b6e9;
            }
            .list_conteiner::-webkit-scrollbar-track {
                border-radius: 10px;
                background-color: #F5F5F5;
            }
            .custom_paginator input {
               width: 26px;
               height: 27px;
               margin-left: 5px;
               background-color: #fff;
            }
        </style>
        <div class="main">
            <div class="name_title">
                <div class="name_position">Список товаров</div>
                <div class="add_position"><a href="/iwaterTest/admin/add_unit"><span class="img_add"></span>Добавить товар</a></div>
            </div>
    <img src="/iwaterTest/css/image/loading-gallery.gif" id="loading" class="loading" alt="" style="width: 141px; display: none;z-index: 9999"/>
        <div class="list_conteiner" style="width: 100%; max-height: 450px; overflow: scroll;">
           <table id="list"></table>
        </div>
        <div id="pager"></div>
        <div class="custom_paginator" style="display: inline-block; float: right; padding: 3px 10px 0px 110px;"></div>
        <table id="categ" style="max-width: 450px; float: left;">

        </table>
        <div id="editor">

        </div>

		<div id="overlay"></div><!-- Пoдлoжкa -->
            <div>
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

                  function createPager() {
                      var currentPage = $("#list").getGridParam('page');
                      var maxPage = $("#list").getGridParam('lastpage');
                      var selectCurrentPage = '';

                      var start = currentPage < 3 ? 1 : currentPage - 2;
                      var finish = currentPage + 2 <= maxPage ? currentPage + 2 : maxPage;
                      var html = '<input type="button" name="" value="<" onclick="goToPage(' + (currentPage - 1) + ');">'; // Строка с пагинотором

                      for (var i = start; i <= finish; i++) {
                        selectCurrentPage = i == currentPage ? ' style="background-color: #74ccea; color: #fff;" ' : '';
                        html += '<input type="button" name="" value="' + i + '" onclick="goToPage(' + i + ')" ' + selectCurrentPage + ' >';
                      }

                      html += '<input type="button" name="" value=">" onclick="goToPage(' + (currentPage + 1) + ')">';
                      $('.custom_paginator').html(html);
                  }

                  function goToPage(page) {
                      $("#list").trigger("reloadGrid",[{page:page}]);
                  }

                	function addNewUnit() {
                     var aboutUn = tinyMCE.activeEditor.getContent();
                		var nameUn = $("#unitName").val();
                		var priceUn = $("#unitPrice").val();
                		var discountUn = $("unitProcent").val();
                		var categoryUn = $("#select_category").val();
                		var galleryUn = $("#unitLinks").val();
                		var logoUn = $("#unitLogo").val();
                		if (nameUn != '' && priceUn != '' && logoUn != '') {
	                		$.ajax({
		                		type: "POST",
		                		url: "/iwaterTest/backend.php?units",
		                		data: {
		                			oper: "add",
		                			id: "0",
		                			name: nameUn,
		                			price: priceUn,
		                			discount: discountUn,
		                			category: categoryUn,
		                			gallery: galleryUn,
		                			logo: logoUn
		                		},
		                		success: function () {
		                			$("#unitName").val('');
	                				$("#unitSubscribe").val('');
	                				$("#unitPrice").val('');
	                				$("unitProcent").val('0');
	                				$("#unitLinks").val('');
	                				$("#unitLogo").val('');
                              tinyMCE.activeEditor.setContent('');
                              $("#list").trigger("reloadGrid");
		                		}
		                	});
		                } else {
		                	alert('Не введены основные данные!');
		                }
                	}

                    var listEdit = " ";

                    $("#unitLogo").keyup( function (e) {
					    if (e.keyCode == 13) {
					        addNewUnit();
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

                    $(function () {
                        var events, cat = [], $grid = $("#list");
                        var lastsel = 0;

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

                                $("#list").jqGrid({
                                    url: "/iwaterTest/backend.php?list_unit",
                                    datatype: "xml",
                                    mtype: "POST",
            					         xmlReader: {
                  							root:"rows",
                  							total:"total",
                  							records:"rows>records",
                  							repeatitems:true
            						      },
                                    colNames: ["Редактировать", "Удалить", "id товара","Внеш.id", "Наименование", "Описание", "Цена", "Скидка в %", "Сссылки на изображения", "Категория", "Логотип"],
                                    colModel: [
                                        {name: "edit", index: "id", width: 100, align: "center", formatter: setEditButton <?php if ($access_level < 2) echo ', hidden: true'; ?>},
                                        {name: "delete", index: "id", width: 60, align: "center", formatter: setDeleteButton <?php if ($access_level < 3) echo ', hidden: true'; ?>},
                                        {name: "id", index: "id", width: 60, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['cn']}},
                                        {name: "gl_id", index: "gl_id", width: 60, align: "center", sorttype: 'integer', search: true, editable: false, searchoptions: { sopt: ['cn']}},
                                        {name: "name", index: "name", width: 220, align: "center", sorttype: 'string', editable: false, editoptions: {cols: 40}, searchoptions: { sopt: ['cn']}},
                                        {name: "about", index: "about", width: 350, align: "center", datatype: 'html', editable: false},
                                        {name: "price", index: "price", width: 100, align: "center", sorttype: 'string', amount:"200.00", search: true, editable: false, searchoptions: { sopt: ['cn']}},
                                        {name: "discount", index: "discount", width: 60, align: "center", sorttype: 'integer', amount:"200.00", search: true, editable: false, searchoptions: { sopt: ['cn']}},
                                        {name: "gallery", index: "gallery", width: 230, align: "center", search: true, editable: false, searchoptions: { sopt: ['cn']}},
                                        {name: "category", index: "category", width: 130, align: "center", edittype: 'select', formatter: 'select', search: true, editable: false, editoptions: { value: listEdit, },  searchoptions: {sopt: ['cn']}},
                                        {name: "logo", index: "logo", width: 80, align: "center", editable: false, }
                  						],
                  						pager: "#pager",
                                    rowNum: 30,
                                    rowList: [30, 50, 100],
                  						sortname: 'id',
                                    viewrecords: true,
                  						sortorder: "desc",
                                    height: 'auto',
                                    ajaxRowOptions: function () {
                                       $("#list").trigger("reloadGrid");
                                     },
                                    onPaging: function () {
                                    },
                                    onSortCol: function () {
                                    },
                                    loadComplete: function () {
                                       createPager();
                                    },
                                    onSelectRow: function (id) {
                                        if (id && id !== lastsel && lastsel != 0) {
                                            jQuery('#list').jqGrid('saveRow', lastsel);
                                            jQuery('#list').jqGrid('editRow', id, true);
                                            lastsel = id;
                                        } else {
                                            if (lastsel == 0) {
                                               jQuery('#list').jqGrid('editRow', id, true);
                                               lastsel = id;
                                            }
                                        }
                                    },
                                    onSortCol: function (rowid, iRow, iCol, e) {
                                       $(".ui-search-toolbar").show();
                                    },
                                    editurl: "/iwaterTest/backend.php?units=1",
                                    gridview: true,
                                    autoencode: false,
                                    caption: "Каталог товаров",
                                    loadonce: false,
                                    sortable: true,
                                    multiselect: false
                            });
                        events = $grid.data("events");

                        if (events && events.reloadGrid && events.reloadGrid.length === 1) {
                            originalReloadGrid = events.reloadGrid[0].handler; // save old
                            $grid.unbind('reloadGrid');
                            $grid.bind('reloadGrid', function (e, opts) {
                                enableMultiselect.call(this, true);
                                originalReloadGrid.call(this, e, opts);
                            });
                        }

                        $("#list").jqGrid('navGrid', "#pager", {edit: true, add: true, del: true}, {}, {}, {},
                        {
                            multipleSearch: true,// Поиск по нескольким полям
                            multipleGroup: true, // Сложный поиск с подгруппами условий
                            showQuery: true
                        });

                        $("#list").jqGrid('filterToolbar', {searchOperators: true});
                        $("#list").jqGrid('setGridHeight', 'auto');
                        $(".ui-search-toolbar").hide();
                        }
                    });
                });

                function setEditButton(options) {

                   return '<a href="/iwaterTest/admin/edit_unit?id=' + options + '"><img src="/iwaterTest/css/image/edit.png" style="cursor: pointer;"></a>';
                }

                function setDeleteButton(options) {
            return '<a><img src="/iwaterTest/css/image/delete.png" style="cursor: pointer;" onclick="if (confirm(\' Подтвердить удаление?\')) {deleteUnit(' + options + ');} else { return false; }"></a>';
         }
                function deleteUnit(val) {
            $.ajax({
               type: "POST",
               url: "/iwaterTest/backend.php",
               data: {
                  delete_unit: val
               },
               success: function () {
                 (alert('Запись успешно удалена'))
                     window.location.reload();

               }
            });
         }
                  // return '<a href="/iwaterTest/backend.php?delete_unit=' + options + '"><img src="/iwaterTest/css/image/delete.png" style="cursor: pointer;" onclick="if (!confirm(\' Подтвердить удаление?\')) { return false; }"></a>';

                function maxLengthName() {
                  if ($('#unitName').val().length == 86) {
                    $('.leng_alert').css("display", "block");
                  } else {
                    $('.leng_alert').css("display", "none");
                  }
                }
                </script>
