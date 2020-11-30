<?php if(isset($perms['add_user'])) { ?> <li><div><p><a href="/iwaterTest/admin/add_user/">Добавить пользователя</a></p></div></li>
<?php } ?>
<?php if(isset($perms['add_role'])) {?> <li><div><p><a href="/iwaterTest/admin/add_role/">Добавить роль</a></p></div></li>
<?php } ?>
<?php if(isset($perms['list_users'])){ ?> <li><div><p><a href="/iwaterTest/admin/list_users/">Список пользователей</a></p></div></li>
<?php } ?>
<?php if(isset($perms['add_client'])) {?> <li><div><p><a href="/iwaterTest/admin/add_client/">Добавить клиента</a></p></div></li>
<?php } ?>
<?php if(isset($perms['list_clients'])){ ?> <li><div><p><a href="/iwaterTest/admin/list_clients/">Список клиентов</a></p></div></li>
<?php } ?>
<?php if(isset($perms['add_order'])) {?> <li><div><p><a href="/iwaterTest/admin/add_order/">Создать заказ</a></p></div></li>
<?php } ?>
<?php if(isset($perms['list_orders'])){ ?> <li><div><p><a href="/iwaterTest/admin/list_orders/">Список заказов</a></p></div></li>
<?php } ?>
<?php if(isset($perms['list_orders_app'])){ ?> <li><div><p><a href="/iwaterTest/admin/list_orders_app/">Необработанные заказы</a></p></div></li>
<?php } ?>
<?php if(isset($perms['list_unit'])){ ?> <li><div><p><a href="/iwaterTest/admin/list_unit/">Список товаров</a></p></div></li>
<?php } ?>
<?php if(isset($perms['add_list'])) {?> <li><div><p><a href="/iwaterTest/admin/add_list/">Добавить путевой лист</a></p></div></li>
<?php } ?>
<?php if(isset($perms['add_list'])) {?> <li><div><p><a href="/iwaterTest/admin/list_lists/">Список путевых листов</a></p></div></li>
<?php } ?>
<?php if(isset($perms['driver_position'])) {?> <li><div><p><a href="/iwaterTest/admin/driver_position/">Координаты водителя</a></p></div></li>
<?php } ?>
<?php if(isset($perms['driver_stat'])) {?> <li><div><p><a href="/iwaterTest/admin/driver_stat/">Статистика водителей</a></p></div></li>
<?php } ?>
<?php if(isset($perms['analytics'])) {?> <li><div><p><a href="/iwaterTest/admin/analytics/">Предиктиваная аналитика</a></p></div></li>
<?php } ?>
<?php if(isset($perms['storage_control'])) {?> <li><div><p><a href="/iwaterTest/admin/storage_control/">Управление складами</a></p></div></li>
<?php } ?>
<?php if(isset($perms['storage_arrival'])) {?> <li><div><p><a href="/iwaterTest/admin/storage_arrival/">Приход на склад</a></p></div></li>
<?php } ?>
<?php if(isset($perms['storage_info'])) {?> <li><div><p><a href="/iwaterTest/admin/storage_info/">Информация о складах</a></p></div></li>
<?php } ?>
<?php if(isset($perms['logs'])) {?> <li><div><p><a href="/iwaterTest/admin/logs/">Логи</a></p></div></li>
<?php } ?>
<?php if(isset($perms['delete_clients'])) {?> <li><div><p><a href="/iwaterTest/admin/delete_clients/">Корзина</a></p></div></li>
<?php } ?>
<?php if(isset($perms['settings'])) {?> <li><div><p><a href="/iwaterTest/admin/settings/">Настройки</a></p></div></li>
<?php } ?>
<?php if(isset($perms['driver_list'])) {?> <hr><li><div><p><a href="/iwaterTest/admin/driver_list/">Путевой лист водителя</a></p></div></li>
<?php } ?>
<?php if(isset($perms['driver_map'])) {?> <li><div><p><a href="/iwaterTest/admin/driver_map/">Карта водителя</a></p></div></li>
<?php } ?>

<?php if(isset($perms['driver_notice'])) {?>
     <li><div><p>
                 <a href="/iwaterTest/admin/driver_notice/">Уведомления
                     <?php if(get_num_notice()>0){ ?>
                     <i class="<?php if(get_num_notice()>0){echo "side_notice";} ?>"><?php echo get_num_notice()?></i>
                     <?php } ?>
                 </a>
     </p></div></li>
<?php } ?>
