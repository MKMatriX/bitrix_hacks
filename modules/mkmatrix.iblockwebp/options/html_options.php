<? foreach ($arAllOptions[$tabControl->tabs[$tabControl->tabIndex - 1]["DIV"]] as $arOption):
	$val = COption::GetOptionString($moduleName, $arOption[0], $arOption[2]);
	$type = $arOption[3];
	?>
	<tr>
		<td
			width="40%"
			nowrap
			<?= ($type[0] == "textarea")? 'class="adm-detail-valign-top"' : ''?>
		>
			<label for="<?= htmlspecialcharsbx($arOption[0]) ?>">
				<?= $arOption[1] ?>:
			</label>
		</td>
		<td width="60%">
			<?
			if ($type[0] == "checkbox"):?>
				<input
					type="checkbox"
					id="<?= htmlspecialcharsbx($arOption[0]) ?>"
					name="<?= htmlspecialcharsbx($arOption[0]) ?>"
					value="Y"<?
					if ($val == "Y") {
						echo " checked";
					} ?>
				/>
			<?
			elseif ($type[0] == "text"):?>
				<input
					type="text"
					size="<?= $type[1] ?>"
					maxlength="255"
					value="<?= htmlspecialcharsbx($val) ?>"
					name="<?= htmlspecialcharsbx($arOption[0]) ?>"
				/>
			<?
			elseif ($type[0] == "number"):?>
				<input
					type="number"
					size="<?= $type[1] ?>"
					maxlength="255"
					value="<?= htmlspecialcharsbx($val) ?>"
					name="<?= htmlspecialcharsbx($arOption[0]) ?>"
				/>
			<?
			elseif ($type[0] == "textarea"):?>
				<textarea
					rows="<?= $type[1] ?>"
					cols="<?= $type[2] ?>"
					name="<?= htmlspecialcharsbx($arOption[0]) ?>"
				><?= htmlspecialcharsbx($val) ?></textarea>
			<?
			elseif ($type[0] == "selectbox"):?>
				<select
					multiple
					name="<?= htmlspecialcharsbx($arOption[0]) ?>[]">
					<?
					$val = explode(",", $val);
					foreach ($type[1] as $key => $value) {
						?>
						<option
							value="<?= $key ?>"<?= (in_array( $key, $val )) ? " selected" : "" ?>
						><?= $value ?></option><?
					}
					?>
				</select>
			<?
			endif ?>
		</td>
	</tr>
<? endforeach ?>