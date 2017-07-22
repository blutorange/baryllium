<form class="bootstrap-parsley">
    <p><?=$this->egettext('filetree.mkdir.heading')?></p>
    <?php
    $this->insert('partials/form/input', [
        'label'          => 'filetree.mkdir.title',
        'id'             => "filetree_mkdir_title",        
        'required'       => true
    ])?>

    <?php
    $this->insert('partials/form/textarea', ['label' => 'register.pass',
        'label'       => 'filetree.mkdir.desc',
        'id'          => "filetree_mkdir_desc",
        'placeholder' => 'filetree.mkdir.hint'])
    ?>
</form>
