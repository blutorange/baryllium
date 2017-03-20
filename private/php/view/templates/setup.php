<?php $this->layout('master', ['title' => $title ?? 'Setup']); ?>

<header>
    <!-- Render messages, when there are any in the header. -->
    <?php
        if (isset($messages) && sizeof($messages) > 0) {
            $this->insert('partials/messages', ['messages' => $messages]);
        }
    ?>
</header>

<?=$this->section('content')?>