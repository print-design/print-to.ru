<?php
if(LoggedIn()):
$find_class = " d-none";
$group_class = "";
$append_class = "";
$string_class = " d-none";
$placeholder = "Поиск по запчастям";
if(filter_input(INPUT_GET, "find") != '') {
    $find_class = " w-100";
    $group_class = " w-100";
    $append_class = " d-none";
    $string_class = "";
    $placeholder = "";
}
?>
<button type="button" class="btn btn-link mr-2 ml-auto<?=$append_class ?>" id="find-append" style="color: #EC3A7A; height: 35px; line-height: 0;"><i class="fas fa-search"></i></button>
<form class="form-inline ml-auto mr-3<?=$find_class ?>" method="get" id="find-form" action="<?=APPLICATION.'/sparepart/' ?>">
    <div class="input-group input-group-sm<?=$group_class ?>" id="find-group">
        <input type="hidden" name="type_id" value="<?=$type_id ?>" />
        <input type="hidden" name="machine_id" value="<?=$machine_id ?>" />
        <input type="text" class="form-control" id="find" name="find" placeholder="<?=$placeholder ?>" />
        <div class="input-group-append">
            <button type="submit" class="btn btn-outline-dark form-control" id="find-submit" style="border-top-right-radius: 5px; border-bottom-right-radius: 5px; height: 35px;">Найти</button>
        </div>
        <div class="position-absolute px-2 align-text-bottom <?=$string_class ?>" style="top: 3px; left: 5px; bottom: 3px; background-color: gray; color: white; border-radius: 4px; padding-top: .2rem;">
        <?= filter_input(INPUT_GET, "find") ?>
            &nbsp;&nbsp;
            <a href="<?=APPLICATION.'/sparepart/?type_id='.$type_id.'&machine_id='.$machine_id ?>"><i class="fas fa-times" style="color: white;"></i></a>
        </div>
        <div class="position-absolute d-none" style="top: 0px; right: 70px; z-index: 10;">
            <button type="button" type="btn" class="btn btn-link" id="find-camera"><i class='fas fa-camera'></i></button>
        </div>
    </div>
</form>
<?php
else:
    echo "<div class='ml-auto'></div>";
endif;
?>