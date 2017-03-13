<?php $this->layout('master', ['title' => $title ?? 'Portal']) ?>

<!-- Include some header -->
<header>
    <!-- Render messages, when there are any in the header. -->
    <?php
        if (isset($messages) && sizeof($messages) > 0) {
            $this->insert('partials/messages', ['messages' => $messages]);
        }
    ?>
</header>

<!-- Include some sidebar / topbar -->
<section id="mainFrame">
    <?=$this->section('content')?>
</section>

<!-- Include some footer -->