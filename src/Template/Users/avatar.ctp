<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    [
        'controller' => 'users',
        'action' => 'view',
        $user->get('id')
    ]
);
$this->end();
?>
<div class="panel">
    <?= $this->Layout->panelHeading($titleForPage, ['pageHeading' => true]) ?>
    <div class='panel-content panel-form'>
        <?php
        echo $this->User->getAvatar($user);
        echo $this->Form->create($user, ['type' => 'file']);
        echo $this->Form->input(
            'avatar',
            ['type' => 'file', 'required' => false]
        );
        echo $this->Form->button(
            __('gn.btn.save.t'),
            ['class' => 'btn btn-submit']
        );
        $avatar = $user->get('avatar');
        if (!empty($avatar)) {
            echo $this->Form->button(
                __('gn.btn.delete.t'),
                [
                    'class' => 'btnLink',
                    'name' => 'avatarDelete',
                    // @td remove style
                    'style' => 'padding-left: 1em',
                    'value' => '1'
                ]
            );
        }
        echo $this->Form->end();
        ?>
    </div>
</div>