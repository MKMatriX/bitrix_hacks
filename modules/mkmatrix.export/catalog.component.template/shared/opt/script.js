if (window.optRequest !== undefined) {
	Object.entries(window.optRequest).map(([article, amount]) => {
		let nodes = document.querySelectorAll(`input[data-article="${article}"]`)
		nodes.forEach(i => i.value = amount)
		nodes.forEach(i => i.closest('[data-entity="item"]').querySelector('.js-check').checked = true)
		if (!nodes.length) {
			console.error(`Не найдет элемент с артикулом "${article}"`)
		}
	})

	document.addEventListener("DOMContentLoaded", () => {
		document.querySelectorAll('.catalog [name="QUANTITY"]').forEach(i => i.dispatchEvent((new Event('change'))))
	})
}

