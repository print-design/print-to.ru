<?php
$php_self = $_SERVER['PHP_SELF'];
$substrings = mb_split("/", $php_self);
$count = count($substrings);
$folder = '';
$file = '';

if($count > 1) {
    $folder = $substrings[$count - 2];
    $file = $substrings[$count - 1];
}

$sparepart_class = '';
$machine_class = '';
$user_class = '';

if($folder == "sparepart") {
    $sparepart_class = " active";
}
elseif($folder == "machine") {
    $machine_class = " active";
}
elseif($folder == "user") {
    $user_class = " active";
}
?>
<div id="left_bar">
    <a href="<?=APPLICATION ?>/" class="left_bar_item logo ui_tooltip right" title="На главную"><img src="<?=APPLICATION ?>/images/logo.svg" /></a>
    <?php
    // Запчасти
    if(IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))):
    ?>
    <a href="<?=APPLICATION ?>/sparepart/" class="left_bar_item ui_tooltip right<?=$sparepart_class ?>" title="Запчасти"><img src="<?=APPLICATION ?>/images/nav_sparepart.svg" style="height: 25px; width: 25px;" /></a>
    <?php
    endif;
    // Машины
    if(IsInRole(array(ROLE_NAMES[ROLE_ADMIN], ROLE_NAMES[ROLE_ENGINEER], ROLE_NAMES[ROLE_MECHANIC]))):
    ?>
    <a href="<?=APPLICATION ?>/machine/" class="left_bar_item ui_tooltip right<?=$machine_class ?>" title="Машины"><img src="<?=APPLICATION ?>/images/nav_machine.svg" style="height: 25px; width: 25px;" /></a>
    <?php
    endif;
    // Пользователи
    if(IsInRole(array(ROLE_NAMES[ROLE_ADMIN]))):
    ?>
    <a href="<?=APPLICATION ?>/user/" class="left_bar_item ui_tooltip right<?=$user_class ?>" title="Пользователи"><img src="<?=APPLICATION ?>/images/nav_user.svg" style="height: 25px; width: 25px;" /></a>
    <?php endif; ?>
</div>