<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<? if (count($unavailable)): ?>
<div class="unavailable">
	<div class="unavailable__title">Недоступны к заказу:</div>
	<ul class="unavailable__list">
		<? foreach ($unavailable as $line): ?>
				<li class="unavailable__item">
					<?=htmlspecialchars($line)?>
				</li>
			<? endforeach; ?>
	</ul>
</div>
<? endif; ?>

<? if (count($errorLines)): ?>
	<div class="unavailable">
		<div class="unavailable__title">Ошибка импорта:</div>
		<ul class="unavailable__list">
			<? foreach ($errorLines as $line): ?>
				<li class="unavailable__item">
					<?=htmlspecialchars($line)?>
				</li>
			<? endforeach; ?>
		</ul>
	</div>
<? endif; ?>