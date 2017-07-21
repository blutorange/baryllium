<?php
    use Moose\Extension\DiningHall\DiningHallMealInterface;
    /* @var $meals DiningHallMealInterface */
?>
<h2><?=$this->e($hallName)?></h2>
<?php if (\sizeof($meals) === 0): ?>
    <span><?=$this->egettext('dashboard.dininghallmenu.none')?></span>
<?php endif;?>
<?php foreach ($meals as $meal): ?>
    <ul class="mensa-meal-list">
        <li>
            <?php if (!empty($meal->getImage())): ?>
                <a href="<?=$meal->getImage()?>" data-type="image" data-toggle="lightbox" data-gallery="mensa-meal" data-title="<?=$this->e($meal->getName())?>">
                    <img src="<?=$meal->getImage()?>" class="mensa-meal-img img-fluid">
                </a>
            <?php endif; ?>
            <span><?=$this->e($meal->getDate()->format($this->gettext('default.date.format')))?>: <?=$this->e($meal->getName())?> (<?=$this->e(\number_format($meal->getPrice()/100, 2))?> €)</span>
        </li>
    </ul>
<?php endforeach; ?>