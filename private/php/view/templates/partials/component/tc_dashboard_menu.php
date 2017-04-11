<?php
    use Moose\Extension\DiningHall\DiningHallMealInterface;
    /* @var $meals DiningHallMealInterface */
    $date = $this->e($meal->getDate()->format($this->gettext('default.datetime.format')));
?>
<?php foreach ($meals as $meal): ?>
    <ul class="mensa-meal">
        <li>
            <img alt="Image for this dining hall meal" href="<?=$meal->getImage()?>"/>
            <span><?=$date?>: <?=$meal->getName()?> (<?=$meal->getPrice()?>)</span>
        </li>
    </ul>
<?php endforeach; ?>