<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'activate'
            || $action === 'deactivate'
            || $action === 'delete'
        ) {
            include_once('licenses/page-list.php');
        } elseif ($action === 'add') {
            include_once('licenses/page-add.php');
        } elseif ($action === 'import') {
            include_once('licenses/page-import.php');
        } elseif ($action === 'edit') {
            include_once('licenses/page-edit.php');
        }
    ?>
</div>