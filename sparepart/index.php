<?php
include '../include/topscripts.php';

// Авторизация
if(!IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))) {
    header('Location: '.APPLICATION.'/unauthorized.php');
}

$type_id = filter_input(INPUT_GET, 'type_id');
$machine_id = filter_input(INPUT_GET, 'machine_id');

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
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        include '../include/head.php';
        ?>
    </head>
    <body>
        <?php
        include '../include/header_sparepart.php';
        ?>
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
                    <th>Дата установки</th>
                    <th>Примечание</th>
                </tr>
                <?php
                $find = filter_input(INPUT_GET, 'find');
                
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
                $sql = "select id, name, place, number, comment from sparepart where machine_id = $machine_id ";
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
                            <form class="d-inline" method="post" action="?type_id=<?=$type_id ?>&machine_id=<?=$machine_id ?>" onsubmit="javascript: return confirm('Действительно удалить?');">
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
                    <td><button class="btn btn-dark btn-sm"><i class="fas fa-plus"></i></button></td>
                    <td><?=$row['place'] ?></td>
                    <td><?=$row['number'] ?></td>
                    <td></td>
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
        </script>
    </body>
</html>