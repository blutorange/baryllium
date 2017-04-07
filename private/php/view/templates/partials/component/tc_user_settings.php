<div class="moose-white">
    <?php
        $this->insert('partials/form/checkbox',
                [
            'label'         => 'option.paging.list.label',
            'name'          => 'option.paging.list',
            'persist'       => 'client'
        ]);
        $this->insert('partials/form/input', [
            'label'         => 'option.post.count.label',
            'name'          => 'option.post.count',
            'persist'       => 'cookie',
            'type'          => 'number',
            'min'           => 10,
            'max'           => 40
        ]);
    ?>
</div>
