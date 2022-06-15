<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'delete'
        ) {
            include_once('generators/page-list.php');
        } elseif ($action === 'add') {
            include_once('generators/page-add.php');
        } elseif ($action === 'edit') {
            include_once('generators/page-edit.php');
        } elseif ($action === 'generate') {
            include_once ('generators/page-generate.php');
        }
    ?>
</div>