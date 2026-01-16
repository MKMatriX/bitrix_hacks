function AjaxForm(form) {
	if (form.ajaxForm !== undefined) {
		return form.ajaxForm
	}
	this.form = form
	this.form.ajaxForm = this
	this.path = this.form.dataset.component
	this.action = this.form.dataset.action
	this.cl = this.form.classList
	this.lastData = undefined
	this.timeout = 777
	return this
}
AjaxForm.prototype = {
	submit: async function () {
		this.body = new FormData(this.form)
		this.body.append('sessid', window.BX.bitrix_sessid())
		let getParams = `?mode=class&c=${this.path}&action=${this.action}`

		this.onBeforeSend()
		this.lastData = this.body
		let res = await fetch(
			"/bitrix/services/main/ajax.php" + getParams,
			{
				method: "POST",
				body: this.body,
			}
		).then(r => r.json())
		this.onAfterSend()

		if (res.status === "error") {
			this.onError(res)
		} else {
			this.onSuccess(res)
		}
		return res
	},
	onSuccess: function (res) {
		this.cl.add('is-success')

		if ("string" === typeof res.data) {
			var successNode = this.form.querySelector('.success-text')
			if (successNode === null) {
				successNode = document.createElement('div')
				successNode.classList.add('success-text')
				this.form.append(successNode)
			}
			successNode.innerText = res.data
		}

		var modal = this.form.closest('.popup')
		if (modal !== null) {
			setTimeout(() => {
				closeModal(modal)
			}, this.timeout);
		}
	},
	onError: function (res) {
		this.cl.add('is-error')
		var errorsNode = this.form.querySelector('.error-text')
		if (errorsNode === null) {
			errorsNode = document.createElement('div')
			errorsNode.classList.add('error-text')
			this.form.append(errorsNode)
		}
		errorsNode.innerHTML = ""
		res.errors.map(e => {
			var node = document.createElement('DIV')
			node.innerText = e.message
			errorsNode.append(node)
		})
	},
	onBeforeSend: function () {
		this.cl.remove('is-success')
		this.cl.remove('is-error')
		this.cl.add('is-loading')
	},
	onAfterSend: function () {
		this.cl.remove('is-loading')
	},
}


document.addEventListener("submit", async (e) => {
	const target = e.target
	var cl = target.classList
	if (!cl.contains('form-ajax')) {
		return;
	}
	e.preventDefault()
	if (target.ajaxForm === undefined) {
		new AjaxForm(target)
	}
	target.ajaxForm.submit()
})