<?php
include '../include/topscripts.php';

// Авторизация
if(!IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))) {
    header('Location: '.APPLICATION.'/unauthorized.php');
}

$type_id = filter_input(INPUT_GET, 'type_id');
$machine_id = filter_input(INPUT_GET, 'machine_id');

// Тип и машина должны быть заданы
if(empty($type_id) || empty($machine_id)) {
    header('Location: '.APPLICATION.'/sparepart/');
}

// Валидация формы
$form_valid = true;
$error_message = '';

$name = '';
$place = '';
$number = null;
$comment = '';

$name_valid = '';
$place_valid = '';
$number_valid = '';

// Обработка отправки формы
if(null !== filter_input(INPUT_POST, 'create_sparepart_submit')) {
    $machine_id = filter_input(INPUT_POST, 'machine_id');
    
    $name = filter_input(INPUT_POST, 'name');
    if(empty($name)) {
        $name_valid = ISINVALID;
        $form_valid = false;
    }
    
    $place = filter_input(INPUT_POST, 'place');
    if(empty($place)) {
        $place_valid = ISINVALID;
        $form_valid = false;
    }
    
    $number = filter_input(INPUT_POST, 'number');
    if(empty($number)) {
        $number_valid = ISINVALID;
        $form_valid = false;
    }
    
    $comment = filter_input(INPUT_POST, 'comment');
    
    if($form_valid) {
        $name = addslashes($name);
        $place = addslashes($place);
        $comment = addslashes($comment);
        
        $sql = "select count(id) from sparepart where machine_id = $machine_id and name = '$name'";
        $fetcher = new Fetcher($sql);
        if($row = $fetcher->Fetch()) {
            if($row[0] != 0) {
                $error_message = "Для этой машины уже есть запчасть с таким названием.";
            }
        }
        
        if(empty($error_message)) {
            $sql = "insert into sparepart(machine_id, name, place, number, comment) values($machine_id, '$name', '$place', $number, '$comment')";
            $executer = new Executer($sql);
            $error_message = $executer->error;
            $id = $executer->insert_id;
        }
        
        if(empty($error_message)) {
            header('Location: '.APPLICATION."/sparepart/?type_id=$type_id&machine_id=$machine_id#sparepart_$id");
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
            <a class="btn btn-light backlink" href="<?=APPLICATION ?>/sparepart/?type_id=<?= $type_id ?>&machine_id=<?=$machine_id ?>">Назад</a>
            <div class="row">
                <div class="col-12 col-lg-6">
                    <h1>Новая запчасть <?=$machine_name ?></h1>
                    <form method="post">
                        <input type="hidden" name="machine_id" value="<?=$machine_id ?>" />
                        <div class="form-group">
                            <label for="name">Наименование запчасти</label>
                            <input type="text" class="form-control<?=$name_valid ?>" name="name" id="name" value="<?= htmlentities($name) ?>" required="required" autocomplete="off" />
                            <div class="invalid-feedback">Наименование запчасти обязательно</div>
                        </div>
                        <div class="form-group">
                            <label for="place">Место установки</label>
                            <input type="text" class="form-control<?=$place_valid ?>" name="place" id="place" value="<?= htmlentities($place) ?>" required="required" autocomplete="off" />
                            <div class="invalid-feedback">Место установки обязательно</div>
                        </div>
                        <div class="form-group">
                            <label for="number">Кол-во установленных</label>
                            <input type="number" min="1" class="form-control w-25<?=$number_valid ?>" name="number" id="number" value="<?=$number ?>" required="required" autocomplete="off" />
                            <div class="invalid-feedback">Кол-во установленных обязательно</div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Примечание</label>
                            <textarea class="form-control" rows="6" name="comment" id="comment"><?= htmlentities($comment) ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark" name="create_sparepart_submit">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        include '../include/footer.php';
        ?>
    </body>
</html>