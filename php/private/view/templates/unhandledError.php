<?php $this->layout('master', ['title' => 'Unexpected error']) ?>

<section id="error-unexpected">
    <h1>Unexpected error</h1>
    <p>We are sorry, but something happened we did not conceive could. See below for some details. That is all I know :C</p>
    <details>
        <summary><?= $this->e($message) ?></summary>
        <pre><?= $this->e($detail) ?></pre>
    </details>
</section>