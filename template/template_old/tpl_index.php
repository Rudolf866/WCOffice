<div class="main">
			<div class="name_title">
                <div class="name_position">Главная</div>
        </div>
        <div class="p_main">
        	<ul class="main_ul">
        		<li class="main_li">
        			<div class="block_name">Заказы</div>
        			<div class="add_position"><a id="add_pos" href="/iwaterTest/admin/add_order/"><span class="img_add"></span>Добавить заказ<a></div>
        		<div class="add_position"><a id="list_pos" href="/iwaterTest/admin/list_orders/"><span class="img_list"></span>Список заказов<a></div>
        		</li>
        		<li class="main_li" >
        			<div class="block_name">Клиенты</div>
        			<div class="add_position"><a id="add_pos" href="/iwaterTest/admin/add_client/"><span class="img_add"></span>Добавить клиента<a></div>
        		<div class="add_position"><a  id="list_pos" href="/iwaterTest/admin/list_clients/"><span class="img_list"></span>Список клиентов<a></div>
        		</li>
        		<li class="main_li">
        			<div class="block_name">Путевые листы</div>
        			<div class="add_position"><a id="add_pos" href="/iwaterTest/admin/trav_list/"><span class="img_add"></span>Добавить путевой<a></div>
        		<div class="add_position"><a id="list_pos" href="/iwaterTest/admin/trav_list/"><span class="img_list"></span>Список путевых<a></div>
        		</li>
        	</ul>
        </div>
    <div>

    <style>
    .p_main{
    margin: 0 auto;
    width: 80%;
    }
    .main_ul{
    list-style: none;
    display: inline-flex;
    }
    .main_li{
	border-radius: 5px;
    margin-right: 40px;
    height: 200px;
    width: 230px;
    background-color: white;
    }
    .block_name{
    color: #015aaa;
    padding-top: 15px;
    padding-bottom: 15px;
    font-size: 20px;
    text-align: center;
    border-bottom: 1px solid #e3e9f1;
    }
    .add_position{
	float: none;
    padding-left: 10px;
    padding-top: 10px;
    font-size: 16px;
    text-decoration: none
    }
    .img_list {
    display: inline-block;
    vertical-align: middle;
    background: url(./css/image/shopping-list.png) no-repeat center;
    width: 20px;
    height: 20px;
    margin-right: 15px;
    color: #fff;
    background-color: #74ccea;
    padding: 10px;
    border-radius: 20px;
}
        #add_pos:hover .img_add{
    background-color: #015aaa;
}
    #list_pos:hover .img_list{
    background-color: #015aaa;
}
    .img_list:hover {
    background-color: #015aaa;
}
    </style>
