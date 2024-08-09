<?php
include '../include/topscripts.php';

// Авторизация
if(!IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))) {
    header('Location: '.APPLICATION.'/unauthorized.php');
}

// Обработка создания машины
$form_valid = true;
$machine_insert_id = null;

if(null !== filter_input(INPUT_POST, 'create_machine_submit')) {
    $name = filter_input(INPUT_POST, 'name');
    $type = filter_input(INPUT_POST, 'type');
    
    if(empty($name)) {
        $error_message = "Не указано наименование машины";
        $form_valid = false;
    }
    
    if(empty($type)) {
        $error_message = "Не указан тип машины";
        $form_valid = false;
    }
    
    $name = addslashes($name);
    $sql = "select count(id) from machine where name = '$name' and type = $type";
    $fetcher = new Fetcher($sql);
    if($row = $fetcher->Fetch()) {
        if($row[0] != 0) {
            $error_message = "Такая машина типа '".MACHINE_TYPE_NAMES[$type]."' уже есть";
            $form_valid = false;
        }
    }
    
    if($form_valid) {
        $sql = "insert into machine(name, type) values ('$name', $type)";
        $executer = new Executer($sql);
        $error_message = $executer->error;
        $machine_insert_id = $executer->insert_id;
        
        if(empty($error_message)) {
            header('Location: '.APPLICATION.'/machine/#machine_'.$machine_insert_id);
        }
    }
}

// Обработка удаления машины
$form_valid = true;

if(null !== filter_input(INPUT_POST, 'delete_machine_submit')) {
    $id = filter_input(INPUT_POST, 'id');
    
    if(empty($id)) {
        $error_message = "Не указан ID машины";
        $form_valid = false;
    }
    
    $sql = "select count(id) from sparepart where machine_id = $id";
    $fetcher = new Fetcher($sql);
    if($row = $fetcher->Fetch()) {
        if($row[0] != 0) {
            $error_message = "Для этой машины есть запчасти";
            $form_valid = false;
        }
    }
    
    if($form_valid) {
        $sql = "delete from machine where id = $id";
        $executer = new Executer($sql);
        
        $error_message = $executer->error;
        
        if(empty($error_message)) {
            header('Location: '.APPLICATION.'/machine/');
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
    </head>
    <body>
        <?php
        include '../include/header.php';
        ?>
        <div id="create_machine" class="modal fade show">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <div class="modal-header">
                            <p class="font-weight-bold" style="font-size: x-large;">Машина</p>
                            <button type="button" class="close create_machine_dismiss" data-dismiss="modal"><i class="fas fa-times" style="color: #EC3A7A;"></i></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">Наименование</label>
                                <input type="text" name="name" id="name" class="form-control" required="required" autocomplete="off" />
                            </div>
                            <div class="form-group">
                                <label for="type">Тип</label>
                                <select name="type" id="type" class="form-control" required="required">
                                    <option value="" hidden="hidden">...</option>
                                    <?php foreach(MACHINE_TYPES as $type): ?>
                                    <option value="<?=$type ?>"><?=MACHINE_TYPE_NAMES[$type] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer" style="justify-content: flex-start;">
                            <button type="submit" class="btn btn-dark" id="create_machine_submit" name="create_machine_submit">Добавить</button>
                            <button type="button" class="btn btn-light create_machine_dismiss" data-dismiss="modal">Отменить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <?php
            if(!empty($error_message)) {
                echo "<div class='alert alert-danger'>$error_message</div>";
            }
            ?>
            <div class="d-flex justify-content-between mb-auto">
                <div>
                    <h1>Машины</h1>
                </div>
                <div>
                    <button class="btn btn-dark" data-toggle="modal" data-target="#create_machine">
                        <i class="fas fa-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Добавить машину
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <?php foreach(MACHINE_TYPES as $type): ?>
                    <h2><?=MACHINE_TYPE_NAMES[$type] ?></h2>
                    <table class="table">
                        <?php
                        $sql = "select id, name from machine where type = $type order by name";
                        $fetcher = new Fetcher($sql);
                        while($row = $fetcher->Fetch()):
                        ?>
                        <tr>
                            <td><a name="machine_<?=$row['id'] ?>" /> <?= htmlentities($row['name']) ?></td>
                            <td class="text-right">
                                <form method="post" onsubmit="javascript: return confirm('Действительно удалить?');">
                                    <input type="hidden" name="id" value="<?=$row['id'] ?>" />
                                    <button type="submit" class="btn btn-link" name="delete_machine_submit"><img src="../images/icons/trash2.svg" /></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
        include '../include/footer.php';
        ?>
        <script>
            $('#create_machine').on('shown.bs.modal', function() {
                $('input:text:visible:first').focus();
            });
            
            $('#create_machine').on('hidden.bs.modal', function() {
                $('input#name').val('');
                $('select#type').val('');
            });
            
            <?php if(null !== filter_input(INPUT_POST, 'create_machine_submit') && empty($error_message) && !empty($machine_insert_id)): ?>
            window.scrollTo(0, $('#s_<?= $machine_insert_id ?>').offset().top - $('#topmost').height());
            <?php endif; ?>
        </script>
    </body>
</html>