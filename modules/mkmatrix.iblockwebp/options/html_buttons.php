<? foreach ($arButtons[$tabControl->tabs[$tabControl->tabIndex - 1]["DIV"]] as $key => $button): ?>
	<tr>
		<td colspan="2">
			<input type="submit" name="<?=$button["NAME"]?>" value="<?=$button["TEXT"]?>"/>
		</td>
	</tr>
<? endforeach; ?>