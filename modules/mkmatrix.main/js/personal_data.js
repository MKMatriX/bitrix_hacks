
function getPersonalData () {
	let body = new FormData()
	body.append('sessid', window.BX.bitrix_sessid())

	let getParams = `?action=mkmatrix:main.main.getPersonalData`

	fetch(
		"/bitrix/services/main/ajax.php" + getParams,
		{
			method: "POST",
			body,
			cache: "no-cache"
		}
	).then(r => r.json())
	.then(res => {
		if (res.status === "error") {
			console.error(res)
			return;
		}

		window.userAuth = res.data.AUTH;
		// updateBasket(res.data.BASKET_ITEMS)
		window.updateFavorite(res.data.FAVORITE_ITEMS)
		// window.updateCompare(res.data.COMPARE_ITEMS)
		BX.onCustomEvent('OnAfterPersonalDataLoaded');
	})
}

function updateBasket(data) {
	localStorage.setItem("basket", JSON.stringify(data));
	window.basketItems = data

	var numProducts = window.basketItems?.length || 0
	var badge = document.querySelector('[data-basket-badge]')
	badge.style.display = (numProducts > 0) ? '' : 'none'
	badge.innerText = numProducts

	BX.onCustomEvent('OnAfterBasketChange');
}

function listGenerator({addSelector, badgeSelector, globalName, addText}) {
	return function (data) {
		var nodes = [...document.querySelectorAll(addSelector)]
		// if (!window.userAuth) {
		// 	nodes.map(n => n.style.display = "none")
		// 	return
		// }
		data = data || []
		data = data.map(id => parseInt(id))

		localStorage.setItem(globalName, JSON.stringify(data || []));
		window[globalName] = data || []

		var numProducts = window[globalName]?.length || 0
		var badge = document.querySelector(badgeSelector)
		if (!!badge) {
			badge.style.display = (numProducts > 0) ? '' : 'none'
			badge.innerText = numProducts;
		}

		nodes.filter(n =>
			!window[globalName].includes(parseInt(n.dataset.id)) &&
			(n.classList.contains("_delete") || n.classList.contains("_active"))
		).map(n => {
			n.classList.remove('_active')
		})

		nodes.filter(n =>
			window[globalName].includes(parseInt(n.dataset.id))
		).map(n => {
			n.classList.add('_active')
		})
	}
}

window.updateFavorite = listGenerator({
	"addSelector": '.js-addToFavorite',
	"badgeSelector": '[data-favorite-badge]',
	"globalName": 'favorite',
	"addText": 'В избранное'
})

window.updateCompare = listGenerator({
	"addSelector": '.js-addToCompare',
	"badgeSelector": '[data-compare-badge]',
	"globalName": 'compare',
	"addText": 'Сравнить'
})

// ;((w) => {
// 	[
// 		"basket",
// 		"favorite",
// 		"compare",
// 	].map((list) => {
// 		var data = []
// 		try {
// 			var data = JSON.parse((localStorage.getItem(list) || "[]")) || []
// 		} catch (error) {
// 			data = []
// 			console.error(error);
// 		}

// 		w["update" + list[0].toUpperCase() + list.slice(1)](data)
// 	})
// })(window)

document.addEventListener("DOMContentLoaded", function () {
	getPersonalData()
});
// BX.addCustomEvent(window, 'OnBasketChange', getPersonalData)
// BX.addCustomEvent(window, 'OnFavoriteChange', getPersonalData)