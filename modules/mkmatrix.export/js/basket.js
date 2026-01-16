
(() => {
	async function parseResponse(response, type) {

		let res = await response.json()
		let data = res.data

		if (res.status === "error") {
			res.errors.map(e => {
				console.error(e.message)
			})
			if (data === null) {
				return;
			}
		}

		// console.log(data)
		if (data == "basket") {
			openBasket()
		}
	}

	function openBasket() {
		window.BX.onCustomEvent('OnBasketChange');
		window.openModal('.popup-basket')
	}


	async function buyItems(id2amount) {
		let body = new FormData()
		body.append('sessid', window.BX.bitrix_sessid())
		body.append('items', JSON.stringify(id2amount))

		let getParams = `?action=mkmatrix:export.basket.basket`

		let response = await fetch(
			"/bitrix/services/main/ajax.php" + getParams,
			{
				method: "POST",
				body,
				cache: "no-cache"
			}
		)

		parseResponse(response)
	}



	if (undefined !== globalClickHandlers) {
		globalClickHandlers["js-buy-items"] = (node) => {
			let cards = [...document.querySelectorAll('[data-entity="item"]')]
			let checkedCards = cards.filter(card => card.querySelector('.js-check').checked)

			let quantityInputs = checkedCards.map(card => card.querySelector('input[name="QUANTITY"]'))

			quantityInputs.filter(i => i.value > 0)

			if (!quantityInputs.length) {
				return;
			}

			let id2amount = Object.fromEntries(
				quantityInputs.map((i) => [
					i.dataset.productId,
					parseFloat(i.value)
				])
			)
			buyItems(id2amount)

		}
	} else {
		console.error("globalClickHandlers not found, import will not work")
	}

})()