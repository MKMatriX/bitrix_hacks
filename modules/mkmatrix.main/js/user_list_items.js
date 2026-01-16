((window, document) => {
	const listBindBuilder = (list, callback) => {
		if (!('' + list).length) {
			console.error('empty list var')
		}

		return async function (node)  {
			let body = new FormData()
			body.append('sessid', window.BX.bitrix_sessid())
			body.append('itemId', parseInt(node.dataset.id))
			body.append('list', list)

			var isDelete = node.classList.contains("_delete") || node.classList.contains("_active");
			var action = isDelete ? "deleteItemFromList" : "addItemToList"

			let getParams = `?action=mkmatrix:main.main.${action}`

			let res = await fetch(
				"/bitrix/services/main/ajax.php" + getParams,
				{
					method: "POST",
					body
				}
			).then(r => r.json())

			if (res.status === "error") {
				return;
			}

			try {
				if ("function" === typeof callback) {
					callback(res.data)
				}
				// BX.onCustomEvent('OnFavoriteChange');
			} catch (error) {
				console.error(error)
			}
		}
	}


	globalClickHandlers['js-addToFavorite'] = listBindBuilder("Favorite", (d) => window.updateFavorite(d))
	globalClickHandlers['js-addToCompare'] = listBindBuilder("Compare", (d) => window.updateCompare(d))


})(window, document)