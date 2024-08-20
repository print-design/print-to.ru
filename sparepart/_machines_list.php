<div class="text-nowrap nav2">
    <?php
    $sql = "select id, name from machine where type = $type_id order by name";
    $fetcher = new Fetcher($sql);
    while($row = $fetcher->Fetch()):
        $machine_class = $machine_id == $row['id'] ? ' active' : '';
        if($machine_id == $row['id']) {
            $machine_name = $row['name'];
        }
    ?>
    <a href="<?= BuildQueryAddRemove('machine_id', $row['id'], 'type_id') ?>" class="mr-4<?=$machine_class ?>"><?=$row['name'] ?></a>
    <?php endwhile; ?>
</div>
<hr />