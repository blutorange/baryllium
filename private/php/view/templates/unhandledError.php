<?php $this->layout('master', ['title' => 'Unexpected error']) ?>

<section id="error-unexpected" class="container-fluid">
    <h1>Unexpected error</h1>
    <p>We are sorry, but something happened we did not conceive could. See below for some details. That is all I know :C</p>
    <details class="panel panel-default">
        <summary class="panel-heading"><?= $this->e($message) ?></summary>
        <pre class="panel-body"><?= $this->e($detail) ?></pre>
    </details>
</section>