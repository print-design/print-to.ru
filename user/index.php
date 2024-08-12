<?php
include '../include/topscripts.php';

// Авторизация
if(!IsInRole(array(ROLE_NAMES[ROLE_ADMIN]))) {
    header('Location: '.APPLICATION.'/unauthorized.php');
}

// Обработка отправки формы - удаление пользователя
if(null !== filter_input(INPUT_POST, 'delete_user_submit')) {
    $id = filter_input(INPUT_POST, 'id');
    $error_message = (new Executer("delete from user where id=$id"))->error;
}

// Обработка отправки формы - смена пароля
$form_valid = true;
$error_message = '';

$user_change_password_old_valid = '';
$user_change_password_old_message = '';
$user_change_password_new_valid = '';
$user_change_password_new_message = '';
$user_change_password_confirm_valid = '';
$user_change_password_confirm_message = '';

$user_change_password_confirm_fio = '';

if(null !== filter_input(INPUT_POST, 'user_change_password_submit')) {
    if(empty(filter_input(INPUT_POST, "user_change_password_old"))) {
        $user_change_password_old_valid = ISINVALID;
        $user_change_password_old_message = "Текущий пароль обязательно";
        $form_valid = false;
    }
    
    if(empty(filter_input(INPUT_POST, 'user_change_password_new'))) {
        $user_change_password_new_valid = ISINVALID;
        $user_change_password_new_message = "Новый пароль обязательно";
        $form_valid = false;
    }
    
    if(empty(filter_input(INPUT_POST, 'user_change_password_confirm'))) {
        $user_change_password_confirm_valid = ISINVALID;
        $user_change_password_confirm_message = "Подтверждение пароля обязательно";
        $form_valid = false;
    }
    
    if(filter_input(INPUT_POST, 'user_change_password_new') != filter_input(INPUT_POST, 'user_change_password_confirm')) {
        $user_change_password_confirm_valid = ISINVALID;
        $user_change_password_confirm_message = "Пароль и его подтверждение не совпадают";
        $form_valid = false;
    }
    
    // Проверка старого пароля
    $user_change_password_id = filter_input(INPUT_POST, "user_change_password_id");
    $user_change_password_old = filter_input(INPUT_POST, "user_change_password_old");
    $sql = "select count(id) count from user where id=$user_change_password_id and password=password('$user_change_password_old')";
    $fetcher = new Fetcher($sql);
    $row = $fetcher->Fetch();
    if($row['count'] == 0) {
        $user_change_password_old_valid = ISINVALID;
        $user_change_password_old_message = "Неправильный текущий пароль";
        $form_valid = false;
        
        $sql = "select last_name, first_name from user where id=$user_change_password_id";
        $fetcher = new Fetcher($sql);
        $row = $fetcher->Fetch();
        $user_change_password_confirm_fio = $row['last_name'].' '.$row['first_name'];
    }
    
    if($form_valid) {
        $user_change_password_new = filter_input(INPUT_POST, "user_change_password_new");
        $sql = "update user set password=password('$user_change_password_new') where id=$user_change_password_id";
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
        include '../include/header.php';
        ?>
        <div id="user_change_password" class="modal fade show">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post">
                        <input type="hidden" id="user_change_password_id" name="user_change_password_id" value="<?= filter_input(INPUT_POST, 'user_change_password_id') ?>" />
                        <div class="modal-header">
                            <div style="font-size: xx-large;">Изменение пароля</div>
                            <button type="button" class="close user_change_password_dismiss" data-dismiss="modal"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="modal-body">
                            <div style="font-size: x-large;">Пользователь: <span id="user_change_password_fio"><?=$user_change_password_confirm_fio ?></span></div>
                            <div class="form-group">
                                <label for="user_change_password_old">Текущий пароль</label>
                                <input type="password" id="user_change_password_old" name="user_change_password_old" class="form-control<?=$user_change_password_old_valid ?>" required="required" />
                                <div class="invalid-feedback"><?=$user_change_password_old_message ?></div>
                            </div>
                            <div class="form-group">
                                <label for="user_change_password_new">Новый пароль</label>
                                <input type="password" id="user_change_password_new" name="user_change_password_new" class="form-control<?=$user_change_password_new_valid ?>" required="required" />
                                <div class="invalid-feedback"><?=$user_change_password_new_message ?></div>
                            </div>
                            <div class="form-group">
                                <label for="user_change_password_confirm">Новый пароль ещё раз</label>
                                <input type="password" id="user_change_password_confirm" name="user_change_password_confirm" class="form-control<?=$user_change_password_confirm_valid ?>" required="required" />
                                <div class="invalid-feedback"><?=$user_change_password_confirm_message ?></div>
                            </div>
                        </div>
                        <div class="modal-footer" style="justify-content: flex-start;">
                            <button type="submit" class="btn btn-primary" id="user_change_password_submit" name="user_change_password_submit" style="width: 150px;">Изменить пароль</button>
                            <button type="button" class="btn user_change_password_dismiss" data-dismiss="modal" style="width: 150px;">Отменить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <?php
            if(null !== filter_input(INPUT_POST, 'user_change_password_submit') && $form_valid && empty($error_message)) {
                echo "<div class='alert alert-success'>Пароль изменен успешно</div>";
            }
            if(!empty($error_message)) {
               echo "<div class='alert alert-danger'>$error_message</div>";
            }
            ?>
            <div class="d-flex justify-content-between mb-auto">
                <div class="p-0">
                    <h1>Пользователи</h1>
                </div>
                <div class="pt-1">
                    <a href="create.php" title="Добавить пользователя" class="btn btn-dark">
                        <i class="fas fa-plus" style="font-size: 12px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Добавить пользователя
                    </a>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th style="border-top: 0;">ФИО</th>
                        <th style="border-top: 0;">Должность</th>
                        <th style="border-top: 0;">Логин</th>
                        <th style="border-top: 0;">E-Mail</th>
                        <th style="border-top: 0;">Телефон</th>
                        <th style="width: 80px; border-top: 0;">Пароль</th>
                        <th style="width: 80px; border-top: 0;">Активный</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "select id, role_id, first_name, last_name, username, email, phone, active from user order by first_name asc";
                    $fetcher = new Fetcher($sql);
                    $error_message = $fetcher->error;
                    
                    while ($row = $fetcher->Fetch()):
                    ?>
                    <tr>
                        <td><?=$row['first_name'].' '.$row['last_name'] ?></td>
                        <td><?=ROLE_LOCAL_NAMES[$row['role_id']] ?></td>
                        <td><?=$row['username'] ?></td>
                        <td><?=$row['email'] ?></td>
                        <td><?=$row['phone'] ?></td>
                        <td class='text-right'>
                            <button type="button" class="btn btn-link user_change_password_open" data-id="<?=$row['id'] ?>" data-fio="<?=$row['last_name'].' '.$row['first_name'] ?>" data-toggle="modal" data-target="#user_change_password">
                                <image src='../images/icons/edit.svg' />
                            </button>
                        </td>
                        <td class='text-right switch'>
                            <?php if(filter_input(INPUT_COOKIE, USER_ID) != $row['id']): ?>
                            <input type="checkbox" data-id="<?=$row['id'] ?>"<?=$row['active'] ? " checked='checked'" : "" ?> />
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php
        include '../include/footer.php';
        ?>
        <script>
            // Заполнение данных о пользователе при открытии формы изменения пароля
            $('.user_change_password_open').click(function(){
                $('#user_change_password_id').val($(this).attr('data-id'));
                $('#user_change_password_fio').text($(this).attr('data-fio'));
                $(document).trigger('keydown'); // чтобы обнулить защиту от двойного нажатия
            });
            
            // Удаление данных о пользователе при закрытии формы изменения пароля
            $('.user_change_password_dismiss').click(function(){
                $('#user_change_password_id').val('');
                $('#user_change_password_fio').text('');
                $('.is-invalid').removeClass('is-invalid');
            });
            
            // Активирование / деактивирование пользователя
            $(".switch input[type='checkbox']").change(function() {
                $.ajax({ url: "_user.php?id=" + $(this).attr('data-id') + "&active=" + $(this).is(':checked') })
                        .fail(function() {
                            alert('Ошибка при установке / снятии флага активности пользователя');
                });
            });
            
            // Открытие формы изменения пароля, если изменение пароля не было удачным
            <?php if(null !== filter_input(INPUT_POST, 'user_change_password_submit') && !$form_valid): ?>
            $('#user_change_password').modal('show');
            <?php endif; ?>
        </script>
    </body>
</html>