
(() => {
	function downloadBlob(blob, name) {
		let file = window.URL.createObjectURL(blob);

			var a = document.createElement("a");
			a.style = "display: none";
			// window.document.body.appendChild(a); // если мы в корзине, то хрень происходит
			a.href = file
			a.download = name
			a.click()

		window.URL.revokeObjectURL(file);
	}

	async function parseResponse(response, type) {
		let contentType = response.headers.get("content-type").toLowerCase()

		if (contentType == "application/vnd.ms-excel") {
			let blob = await response.blob()
			downloadBlob(blob, `export_${type}.xlsx`)
		} else if (contentType == "text/csv" || contentType == "text/csv;charset=utf-8") {
			let blob = await response.blob()
			downloadBlob(blob, `export_${type}.csv`)
		} else { // if is just a json
			let res = await response.json()

			if (res.status === "error") {
				res.errors.map(e => {
					console.error(e.message)
				})
				return;
			}

			let data = res.data

			console.log(data);
		}
	}

	function addSectionParams(body, {sectionId, searchQuery, filterPath, sort }) {
		body.append('sectionId', sectionId)
		body.append('searchQuery', searchQuery)
		body.append('filterPath', filterPath)
		body.append('sort', sort)
	}

	function addIds(body) {
		document.querySelectorAll('.catalog [name="QUANTITY"]')
			.forEach(input => body.append("products[" + input.dataset.productId + "]", parseFloat(input.value)))
	}

	async function exportCatalog(format, type, params = {}) {
		let body = new FormData()
		body.append('sessid', window.BX.bitrix_sessid())

		if (type == "section") {
			addSectionParams(body, params)
		} else if (type == "favorite") {
			body.append('sort', sort)
		} else if (type == "ids") {
			addIds(body)
		}

		let getParams = `?action=mkmatrix:export.export_${format}.${type}`

		let response = await fetch(
			"/bitrix/services/main/ajax.php" + getParams,
			{
				method: "POST",
				body,
				cache: "no-cache"
			}
		)

		parseResponse(response, type)
	}

	function runExport(event, format, node) {
		event.stopPropagation()
		event.preventDefault()

		let isFavorite = node.dataset.favorite !== undefined
		let isBasket = node.dataset.basket !== undefined
		let isIds = node.dataset.ids !== undefined

		if (isFavorite) {
			let sort = node.dataset.sort || ""
			exportCatalog(format, "favorite", { sort })
		} else if (isBasket) {
			exportCatalog(format, "basket")
		} else if (isIds) {
			exportCatalog(format, "ids")
		} else {
			sectionExport(format, node)
		}
	}

	function sectionExport(format, node) {
		let sectionId = node.dataset.sectionId || 0
		let filterPath = node.dataset.filterPath || ""
		let sort = node.dataset.sort || ""
		let params = new URLSearchParams(document.location.search);
		let searchQuery = params.get("search") || "";

		exportCatalog(format, "section", { sectionId, searchQuery, filterPath, sort })
	}

	document.addEventListener("DOMContentLoaded", () => {
		let xls = document.querySelector(".js-export-xls")
		let csv = document.querySelector(".js-export-csv")

		if (xls) {
			xls.addEventListener('click', (e) => runExport(e, "xls", xls))
		}
		if (csv) {
			csv.addEventListener('click', (e) => runExport(e, "csv", csv))
		}
	});

	window.runExport = runExport
})()