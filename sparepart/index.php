<?php
include '../include/topscripts.php';

// Авторизация
if(!IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))) {
    header('Location: '.APPLICATION.'/unauthorized.php');
}

$type_id = filter_input(INPUT_GET, 'type_id');
$machine_id = filter_input(INPUT_GET, 'machine_id');
$find = filter_input(INPUT_GET, 'find');

// Если не указаны type_id или machine_id перенаправляем на первые из них
if(empty($type_id) || empty($machine_id)) {
    if(empty($type_id)) {
        $type_id = MACHINE_TYPE_PRINTERS;
    }
    
    if(empty($machine_id)) {
        $sql = "select id from machine where type = $type_id order by name limit 1";
        $fetcher = new Fetcher($sql);
        if($row = $fetcher->Fetch()) {
            $machine_id = $row['id']; 
        }
        else {
            $machine_id = "NULL";
        }
    }
    
    header("Location: ?type_id=$type_id&machine_id=$machine_id");
}

// Обработка добавления поставщика
$form_valid = false;
$vendor_insert_id = null;

if(null !== filter_input(INPUT_POST, 'create_vendor_submit')) {
    $sparepart_id = filter_input(INPUT_POST, 'sparepart_id');
    $vendor = filter_input(INPUT_POST, 'vendor');
    $vendor = htmlentities($vendor);
    
    $vendor_id = null;
    
    $sql = "select id from vendor where name = '$vendor'";
    $fetcher = new Fetcher($sql);
    $error_message = $fetcher->error;
    if($row = $fetcher->Fetch()) {
        $vendor_id = $row['id'];
    }
    
    if(empty($error_message)) {
        if(empty($vendor_id)) {
            $sql = "insert into vendor (name) values ('$vendor')";
            $executer = new Executer($sql);
            $error_message = $executer->error;
            $vendor_insert_id = $executer->insert_id;
        
            if(empty($error_message)) {
                $sql = "insert into vendor_sparepart (vendor_id, sparepart_id) values ($vendor_insert_id, $sparepart_id)";
                $executer = new Executer($sql);
                $error_message = $executer->error;
            }
        }
        else {
            $count = 0;
            $sql = "select count(id) from vendor_sparepart where vendor_id = $vendor_id and sparepart_id = $sparepart_id";
            $fetcher = new Fetcher($sql);
            $error_message = $fetcher->error;
            if($row = $fetcher->Fetch()) {
                $count = $row[0];
            }
            
            if($count == 0 && empty($error_message)) {
                $sql = "insert into vendor_sparepart (vendor_id, sparepart_id) values ($vendor_id, $sparepart_id)";
                $executer = new Executer($sql);
                $error_message = $executer->error;
            }
        }
    }
}

// Обработка удаления поставщика
$form_valid = true;

if(null !== filter_input(INPUT_POST, 'vendor_remove_submit')) {
    $vendor_id = filter_input(INPUT_POST, 'vendor_id');
    
    if(empty($vendor_id)) {
        $error_message = "Не указан ID поставщика";
        $form_valid = false;
    }
    
    $sparepart_id = filter_input(INPUT_POST, 'sparepart_id');
    
    if(empty($sparepart_id)) {
        $error_message = "Не указан ID запчасти";
        $form_valid = false;
    }
    
    if($form_valid) {
        $sql = "delete from vendor_sparepart where vendor_id = $vendor_id and sparepart_id = $sparepart_id";
        $executer = new Executer($sql);
        $error_message = $executer->error;
    }
}

// Обработка приёма запчастей на склад
$form_valid = true;

if(null !== filter_input(INPUT_POST, 'stock_in_submit')) {
    $sparepart_id = filter_input(INPUT_POST, 'sparepart_id');
    
    if(empty($sparepart_id)) {
        $error_message = "Не указан ID запчасти";
        $form_valid = false;
    }
    
    $stock_in = filter_input(INPUT_POST, 'stock_in');
    
    if(empty($stock_in)) {
        $error_message = "Не указано количество принятых запчастей";
        $form_valid = false;
    }
    
    $user_id = GetUserId();
    
    if($form_valid) {
        $sql = "insert into stock_history (sparepart_id, value, is_in, user_id) values ($sparepart_id, $stock_in, 1, $user_id)";
        $executer = new Executer($sql);
        $error_message = $executer->error;
        
        if(empty($error_message)) {
            $sql = "update sparepart set stock = stock + $stock_in where id = $sparepart_id";
            $executer = new Executer($sql);
            $error_message = $executer->error;
        }
    }
}

// Обработка взятия запчастей со склада
$form_valid = true;

if(null !== filter_input(INPUT_POST, 'stock_out_submit')) {
    $sparepart_id = filter_input(INPUT_POST, 'sparepart_id');
    
    if(empty($sparepart_id)) {
        $error_message = "Не указан ID запчасти";
        $form_valid = false;
    }
    
    $stock_out = filter_input(INPUT_POST, 'stock_out');
    
    if(empty($stock_out)) {
        $error_message = "Не указано количество взятых запчастей";
        $form_valid = false;
    }
    
    $sql = "select stock from sparepart where id = $sparepart_id";
    $fetcher = new Fetcher($sql);
    if($row = $fetcher->Fetch()) {
        if($row['stock'] < $stock_out) {
            $error_message = "Превышено количество остатка на складе";
            $form_valid = false;
        }
    }
    
    $user_id = GetUserId();
    
    if($form_valid) {
        $sql = "insert into stock_history (sparepart_id, value, is_in, user_id) values($sparepart_id, $stock_out, 0, $user_id)";
        $executer = new Executer($sql);
        $error_message = $executer->error;
        
        if(empty($error_message)) {
            $sql = "update sparepart set stock = stock - $stock_out where id = $sparepart_id";
            $executer = new Executer($sql);
            $error_message = $executer->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        include '../include/head.php';
        ?>
        <style>
            ul.ui-autocomplete {
                z-index: 1100;
            }
        </style>
    </head>
    <body>
        <?php
        include '../include/header_sparepart.php';
        ?>
        <?php
        // Формы добавления продавца / производителя
        $sql = "select id, name, stock from sparepart where machine_id = $machine_id ";
        if(!empty($find)) {
            $find = addslashes($find);
            $sql .= "and name like '%$find%' ";
        }
        $sql .= "order by name";
        $fetcher = new Fetcher($sql);
        $index = 0;
        while($row = $fetcher->Fetch()):
        ?>
        <div id="create_vendor_<?=$row['id'] ?>" class="modal fade show create_vendor">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="sparepart_id" value="<?=$row['id'] ?>" />
                        <input type="hidden" name="scroll" />
                        <div class="modal-header">
                            <p class="font-weight-bold" style="font-size: x-large;"><?=$row['name'] ?></p>
                            <button type="button" class="close create_vendor_<?=$row['id'] ?>_dismiss" data-dismiss="modal"><i class="fas fa-times" style="color: #EC3A7A;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="vendor">Продавец / Производитель</label>
                                <input type="text" class="form-control vendors" name="vendor" required="required" />
                            </div>
                        </div>
                        <div class="modal-footer" style="justify-content: flex-start;">
                            <button type="submit" class="btn btn-dark" name="create_vendor_submit" style="width: 150px;">Добавить</button>
                            <button type="button" class="btn btn-light create_vendor_<?=$row['id'] ?>_dismiss" data-dismiss="modal" style="width: 150px;">Отменить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="stock_in_<?=$row['id'] ?>" class="modal fade show stock_in">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="sparepart_id" value="<?=$row['id'] ?>" />
                        <input type="hidden" name="scroll" />
                        <div class="modal-header">
                            <p class="font-weight-bold" style="font-size: x-large">Приход(<?=$row['name'] ?>)</p>
                            <button type="button" class="close stock_in_<?=$row['id'] ?>_dismiss" data-dismiss="modal"><i class="fas fa-times" style="color: #EC3A7A;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="stock_in">Принять на склад</label>
                                <div class="input-group">
                                    <input type="number" min="1" class="form-control" name="stock_in" required="required" />
                                    <div class="input-group-append"><span class="input-group-text">шт</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="justify-content: flex-start;">
                            <button type="submit" class="btn btn-dark" name="stock_in_submit" style="width: 150px;">OK</button>
                            <button type="button" class="btn btn-light stock_in_<?=$row['id'] ?>_dismiss" data-dismiss="modal" style="width: 150px;">Отменить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="stock_out_<?=$row['id'] ?>" class="modal fade show stock_out">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" name="sparepart_id" value="<?=$row['id'] ?>" />
                        <input type="hidden" name="scroll" />
                        <div class="modal-header">
                            <p class="font-weight-bold" style="font-size: x-large">Расход(<?=$row['name'] ?>)</p>
                            <button type="button" class="close stock_out_<?=$row['id'] ?>_dismiss" data-dismiss="modal"><i class="fas fa-times" style="color: #EC3A7A;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="stock_out">Взять со склада</label>
                                <div class="input-group">
                                    <input type="number" min="1" max="<?=$row['stock'] ?>" class="form-control" name="stock_out" required="required" />
                                    <div class="input-group-append"><span class="input-group-text">шт</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="justify-content: flex-start;">
                            <button type="submit" class="btn btn-dark" name="stock_out_submit" style="width: 150px;">OK</button>
                            <button type="button" class="btn btn-light stock_out_<?=$row['id'] ?>_dismiss" data-dismiss="modal" style="width: 150px;">Отменить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <div class="container-fluid">
            <?php
            $machine_name = '';
            include './_machines_list.php';
            
            if(!empty($error_message)) {
                echo "<div class='alert alert-danger'>$error_message</div>";
            }
            ?>
            <div class="d-flex justify-content-between">
                <div><h1>Запчасти <?=$machine_name ?></h1></div>
                <div><a href="create.php?type_id=<?=$type_id ?>&machine_id=<?=$machine_id ?>" class="btn btn-dark"><i class="fas fa-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Добавить запчасть</a></div>
            </div>
            <table class="table">
                <tr>
                    <th>№</th>
                    <th>Наименование запчасти</th>
                    <th>Продавец / Производитель</th>
                    <th></th>
                    <th>Место установки</th>
                    <th>Кол-во установленных</th>
                    <th>Кол-во остатка на складе</th>
                    <th></th>
                    <th>Дата установки</th>
                    <th>Примечание</th>
                </tr>
                <?php
                // Продавцы запчастей
                $sql = "select vs.vendor_id, vs.sparepart_id, v.name vendor "
                        . "from vendor_sparepart vs "
                        . "inner join vendor v on vs.vendor_id = v.id "
                        . "inner join sparepart s on vs.sparepart_id = s.id "
                        . "where s.machine_id = $machine_id ";
                if(!empty($find)) {
                    $find = addslashes($find);
                    $sql .= "and s.name like '%$find%' ";
                }
                $sql .= "order by v.name";
                $fetcher = new Fetcher($sql);
                $vendors = array();
                while($row = $fetcher->Fetch()) {
                    if(!key_exists($row['sparepart_id'], $vendors)) {
                        $vendors[$row['sparepart_id']] = array();
                    }
                    
                    array_push($vendors[$row['sparepart_id']], array('id' => $row['vendor_id'], 'name' => $row['vendor']));
                }
                
                // Запчасти
                $sql = "select id, name, place, number, stock, comment from sparepart where machine_id = $machine_id ";
                if(!empty($find)) {
                    $find = addslashes($find);
                    $sql .= "and name like '%$find%' ";
                }
                $sql .= "order by name";
                $fetcher = new Fetcher($sql);
                $index = 0;
                while($row = $fetcher->Fetch()):
                ?>
                <tr>
                    <td><?= ++$index ?><a name="sparepart_<?=$row['id'] ?>" /></td>
                    <td><?=$row['name'] ?></td>
                    <td>
                        <?php
                        if(key_exists($row['id'], $vendors)):
                        foreach($vendors[$row['id']] as $vendor):
                        ?>
                        <div>
                            <?=$vendor['name'] ?>
                            &nbsp;
                            <form class="d-inline" method="post" onsubmit="javascript: return confirm('Действительно удалить?');">
                                <input type="hidden" name="scroll" />
                                <input type="hidden" name="vendor_id" value="<?=$vendor['id'] ?>" />
                                <input type="hidden" name="sparepart_id" value="<?=$row['id'] ?>" />
                                <button type="submit" class="btn btn-sm btn-link" name="vendor_remove_submit"><i class="fas fa-times"></i></button>
                            </form>
                        </div>
                        <?php
                        endforeach;
                        endif;
                        ?>
                    </td>
                    <td><button class="btn btn-dark btn-sm" data-toggle="modal" data-target="#create_vendor_<?=$row['id'] ?>" title="Добавить продавца / производителя" data-placement="right"><i class="fas fa-plus"></i></button></td>
                    <td><?=$row['place'] ?></td>
                    <td><?=$row['number'] ?></td>
                    <td><?=$row['stock'] ?></td>
                    <td>
                        <button class="btn btn-dark btn-sm tooltip-left" data-toggle="modal" data-target="#stock_in_<?=$row['id'] ?>" title="Принять на склад"><i class="fas fa-plus"></i></button>
                        <?php if($row['stock'] > 1): ?>
                        <button class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#stock_out_<?=$row['id'] ?>" title="Взять со склада"><i class="fas fa-minus"></i></button>
                        <?php endif; ?>
                    </td>
                    <td></td>
                    <td>
                        <div class="d-flex justify-content-start">
                            <div class="pr-2 comment_pen">
                                <a href="javascript: void(0);" onclick="EditComment(event);">
                                    <image src="../images/icons/edit1.svg" title="Редактировать" />
                                </a>
                            </div>
                            <div class="comment_text"><?=$row['comment'] ?></div>
                        </div>
                        <div class="d-none comment_input">
                            <input type="text" 
                                   class="form-control" 
                                   value="<?=$row['comment'] ?>" 
                                   onkeydown="javascript: if(event.key == 'Enter') { SaveComment(event, <?=$row['id'] ?>); }" 
                                   onfocusout="javascript: SaveComment(event, <?=$row['id'] ?>);" /> 
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <?php
        include '../include/footer.php';
        ?>
        <script>
            function EditComment(ev) {
                $(ev.target).parents('td').children('.d-flex').children('.comment_pen').addClass('d-none');
                $(ev.target).parents('td').children('.d-flex').children('.comment_text').addClass('d-none');
                $(ev.target).parents('td').children('.comment_input').removeClass('d-none');
                $(ev.target).parents('td').children('.comment_input').children('input').focus();
                
                input = $(ev.target).parents('td').children('.comment_input').children('input');
                input.prop("selectionStart", input.val().length);
                input.prop("selectionEnd", input.val().length);
            }
            
            function SaveComment(ev, id) {
                text = $(ev.target).val();
                $(ev.target).val('');
                $.ajax({ url: "_edit_comment.php?id=" + id + "&text=" + text })
                        .done(function(data) {
                            $(ev.target).val(data);
                            $(ev.target).parents('.comment_input').addClass('d-none');
                            $(ev.target).parents('td').children('.d-flex').children('.comment_text').html(data);
                            $(ev.target).parents('td').children('.d-flex').children('.comment_pen').removeClass('d-none');
                            $(ev.target).parents('td').children('.d-flex').children('.comment_text').removeClass('d-none');
                        })
                        .fail(function() {
                            alert('Ошибка при редактировании комментария')
                        });
            }
            
            function VendorsAutocpmplete() {
                var vendors = [
                    <?php
                    $vendors = array();
                    $sql = "select name from vendor order by name";
                    $fetcher = new Fetcher($sql);
                    while($row = $fetcher->Fetch()) {
                        array_push($vendors, '"'.addslashes($row['name']).'"');
                    }
                    
                    echo implode(",", $vendors);
                    ?>
                ];
                $("input.vendors").autocomplete({
                    source: vendors
                });
            }
            
            VendorsAutocpmplete();
            
            $('.create_vendor').on('shown.bs.modal', function() {
                $('input:text:visible[name=vendor]').focus();
            });
            
            $('.create_vendor').on('hidden.bs.modal', function() {
                $('input[name=vendor]').val('');
            });
            
            $('.stock_in').on('shown.bs.modal', function() {
                $('input[name=stock_in]').focus();
            });
            
            $('.stock_in').on('hidden.bs.modal', function() {
                $('input[name=stock_in]').val('');
            });
            
            $('.stock_out').on('shown.bs.modal', function() {
                $('input[name=stock_out]').focus();
            });
            
            $('.stock_out').on('hidden.bs.modal', function() {
                $('input[name=stock_out]').val('');
            });
            
            <?php if(null !== filter_input(INPUT_POST, 'create_machine_submit') && empty($error_message) && !empty($machine_insert_id)): ?>
            window.scrollTo(0, $('#s_<?= $machine_insert_id ?>').offset().top - $('#topmost').height());
            <?php endif; ?>
        </script>
    </body>
</html>