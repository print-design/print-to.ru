<?php
include '../include/left_bar.php';
?>
<div class="container-fluid header">
    <nav class="navbar navbar-expand-sm justify-content-end">
        <ul class="navbar-nav">
            <?php
            foreach(MACHINE_TYPES as $type):
                $type_class = '';
                if($type_id == $type) {
                    $type_class = ' disabled';
                }
            ?>
            <li class="nav-item">
                <a class="nav-link text-nowrap<?=$type_class ?>" href="<?= BuildQueryAddRemove('type_id', $type, 'machine_id') ?>"><?=MACHINE_TYPE_NAMES[$type] ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="ml-auto"></div>
        <?php
        if(file_exists('find.php')) {
            include 'find.php';
        }
        else {
            echo "<div class='ml-auto'></div>";
        }
        
        include '../include/header_right.php';
        ?>
    </nav>
</div>
<div id="topmost"></div>