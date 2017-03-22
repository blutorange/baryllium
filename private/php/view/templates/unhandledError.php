<?php $this->layout('master', ['title' => $this->gettext($title)]) ?>

<section id="error-unexpected" class="container-fluid">
    <h1><?= $this->egettext($title) ?></h1>
    <details class="panel panel-default">
        <summary class="panel-heading"><?= $this->e($message) ?></summary>
        <pre class="panel-body"><?= $this->e($detail) ?></pre>
    </details>
</section>