<?php
include 'include/topscripts.php';
?>
<!DOCTYPE html>
<html>
    <head>
        <?php
        include 'include/head.php';
        ?>
        <style>
            #topmost {
                height: 85px;
            }
        </style>
    </head>
    <body>
        <?php
        include 'include/header.php';
        ?>
        <div class="container-fluid">
            <?php
            if(!empty($error_message)) {
               echo "<div class='alert alert-danger mt-3'>$error_message</div>";
            }
            ?>
            <h1>Принт-Дизайн</h1>
            <h2>Учёт запчастей</h2>
        </div>
        <?php
        include 'include/footer.php';
        ?>
    </body>
</html>