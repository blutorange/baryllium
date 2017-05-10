<?php if ($section->isAvailableToUser($this->getUser())): ?>
    <li class="dropdown">
        <li class="dropdown <?= $this->getActiveSection()->isChildOfOrSame($section) ? 'active' : ''?>">
            <a href="#"
                class="<?=$section->getId()?> dropdown-toggle"
                data-toggle="dropdown"
                role="button"
                aria-haspopup="true"
                aria-expanded="false"
            >
                <?= $this->e($section->getName())?>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
                <?php foreach (($items ?? []) as $item): ?>
                    <?= $item ?>
                <?php endforeach; ?>
            </ul>
        </li>
    </li>
<?php endif; ?>