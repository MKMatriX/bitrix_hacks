
(() => {
	let optTextareaName = "OPT"
	let delimiter = " / "

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
		sendToOpt(data)
	}

	function sendToOpt (data) {
		var form = document.createElement('form');
		form.style.display = 'none'
		form.method = 'POST'
		form.action = '/catalog/opt/'

		var input = document.createElement('input');
		input.name = "sessid"
		input.value = window.BX.bitrix_sessid()
		form.appendChild(input)

		var articlesInput = document.createElement('textarea');
		articlesInput.name = optTextareaName
		articlesInput.value = Object.entries(data).map(([k, v]) => k + delimiter + v).join("\n")
		form.appendChild(articlesInput)

		document.body.appendChild(form)
		form.submit()
	}

	function askForFile(format) {
		let multiple = false
		let contentType = "." + format
		if (format == "xls") {
			contentType += ", .xlsx"
		}
		let input = document.createElement('input')
		input.type = 'file'
		input.multiple = multiple
		input.accept = contentType

		input.onchange = () => {
			let files = Array.from(input.files);
			files = multiple ? files : files[0]
			importCatalog(format, {file: files})
		};

		input.click()
	}

	async function importCatalog(format, params = {}) {
		let body = new FormData()
		body.append('sessid', window.BX.bitrix_sessid())
		body.append('file', params.file)

		let getParams = `?action=mkmatrix:export.import_${format}.import`

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
		["xls", "csv"].map(format => {
			globalClickHandlers[`js-import-${format}`] = (node) => {
				askForFile(format)
			}
		})
	} else {
		console.error("globalClickHandlers not found, import will not work")
	}

})()