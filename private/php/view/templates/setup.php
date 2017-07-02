<?php $this->layout('master', ['title' => $title ?? 'Setup']); ?>
<header class="container" style="padding-top: 1em;">
    <!-- Render messages, when there are any in the header. -->
    <?php
        if (isset($messages) && sizeof($messages) > 0) {
            $this->insert('partials/messages', ['messages' => $messages]);
        }
    ?>
</header>

<div id="layout_setup">
    <?=$this->section('content')?>
</div>