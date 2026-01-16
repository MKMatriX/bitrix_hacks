<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<div class="wholesale__search-block">
	<form action="" method="post" name="opt">
		<div class="wholesale__title">Подбор товаров по артикулам</div>
		<div class="wholesale__line form__line">
			<textarea
				required
				autocomplete="off"
				name="OPT"
				placeholder="Введите в каждой строке артикул товара и его количество после символа <?=OPT_DELIMITER?>. Например WCH20C <?=OPT_DELIMITER?> 4.  "
				class="form__input"
			><?=$inputValue?></textarea>
			<svg class="form__clear-svg">
				<use xlink:href="<?=SITE_TEMPLATE_PATH?>/img/icons/icons.svg#input_clear"></use>
			</svg>
		</div>
		<div class="wholesale__btns">
			<button
				type="button"
				class="wholesale__btn btn-15 btn-15_blck js-import-xls"
			>Загрузить XLS для поиска товаров</button>
			<button
				type="submit"
				class="wholesale__btn btn-15"
			>Найти в каталоге</button>
		</div>
	</form>
</div>