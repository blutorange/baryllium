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
        $this->insert('partials/form/dropdown', [
            'label'         => 'option.edit.mode.label',
            'name'          => 'option.edit.mode',
            'persist'       => 'client',
            'options'       => [
                'popup' => 'option.edit.mode.popup',
                'inline' => 'option.edit.mode.inline'
            ]
        ]);        
    ?>
</div>
