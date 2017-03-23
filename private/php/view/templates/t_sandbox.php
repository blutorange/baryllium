<?php $this->layout('portal', ['title' => 'Forums']); ?>

<html>
    <body>
<form>
    <input name="title" type="text" placeholder="Title?" />
    
    <?php
    $this->insert('partials/form/markdown',['label' => 'post.new.content.label',
        'name' => 'content', 'required' => true])
    ?> 

    
    <label class="checkbox">
      <input name="publish" type="checkbox"> Publish
    </label>
    <hr/>
    <button type="submit" class="btn">Submit</button>
</form>
        </body>
    </html>