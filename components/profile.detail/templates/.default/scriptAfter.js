function setAddressLooksActive(node) {
	let list = [...document.querySelectorAll('.js-address-list .js-profile')]
	list.filter(n => n.classList.contains('active')).map(n => {
		n.classList.remove('active')
		var setDefaultButton = n.querySelector('input.js-set-profile-default')
		setDefaultButton.checked = false
	})
	node.classList.add('active')
	var setDefaultButton = node.querySelector('input.js-set-profile-default')
	setDefaultButton.checked = true
}

globalClickHandlers['js-set-profile-default'] = async (node) => {
	var container = node.closest('.js-profile')

	let body = new FormData()
	body.append('sessid', window.BX.bitrix_sessid())
	body.append('ID', parseInt(container.dataset.profileId))
	let getParams = `?mode=class&c=mkmatrix:profile.detail&action=profileSetDefault`

	let res = await fetch(
		"/bitrix/services/main/ajax.php" + getParams,
		{
			method: "POST",
			body,
		}
	).then(r => r.json())

	if (res.status === "error") {
		console.log(res.errors);
	} else {
		setAddressLooksActive(container)
	}
}

var userForm = new AjaxForm(document.forms.updateUser)
userForm.onSuccess = function (res) {
	this.__proto__.onSuccess.call(this, res)
	document.querySelector('.js-user-name').innerText = this.lastData.get('name')
	document.querySelector('.js-user-email').innerText = this.lastData.get('email')
	document.querySelector('.js-user-phone').innerText = this.lastData.get('phone')
}

;[...document.forms].filter(f => f.name == "addProfile").map(form => {
	var addProfileForm = new AjaxForm(form)
	addProfileForm.onSuccess = function (res) {
		this.__proto__.onSuccess.call(this, res)
		var newNode = document.querySelector('.js-address-template').content.children[0].cloneNode(true)
		var pid = res.data.profile.ID
		newNode.dataset.profileId = pid
		var input = newNode.querySelector("[name=profileId]")
		input.id = "profile" + pid
		input.value = pid
		newNode.querySelector("label.addresses__label").for = "profile" + pid
		newNode.querySelector(".js-profile-name").innerText = res.data.profile.NAME || "Новый профиль"
		var typeNode = newNode.querySelector(".js-type")
		typeNode.innerText = res.data.typeName
		typeNode.dataset.tid = res.data.typeId

		;[...this.lastData].filter(([k, v]) => k.slice(0, 13) == "profileToSave").map(([k, v]) => {
			let key = k.match(/\[(.*)\]/)[1]
			let prop = newNode.querySelector('.js-property[data-code="' + key + '"]')
			if (!prop) {
				return
			}
			prop.querySelector('.js-property-value').innerText = v
			prop.style.display = ''
		})

		document.querySelector('.js-address-list').prepend(newNode)
		setAddressLooksActive(newNode)
	}
})

;[...document.forms].filter(f => f.name == "updateProfile").map(form => {
	var updateProfileForm = new AjaxForm(form)
	updateProfileForm.onSuccess = function (res) {
		this.__proto__.onSuccess.call(this, res)
		var list = document.querySelectorAll('.js-address-list .js-profile');
		var container = [...list].find(a => a.dataset.profileId == this.lastData.get('ID'))

		container.querySelector(".js-profile-name").innerText = res.data.profile.NAME || "Новый профиль"
		var typeNode = container.querySelector(".js-type")
		typeNode.innerText = res.data.typeName
		typeNode.dataset.tid = res.data.typeId

		;[...this.lastData].filter(([k, v]) => k.slice(0, 13) == "profileToSave").map(([k, v]) => {
			let key = k.match(/\[(.*)\]/)[1]
			let prop = container.querySelector('.js-property[data-code="' + key + '"]')
			if (!prop) {
				return
			}
			prop.querySelector('.js-property-value').innerText = v
		})
	}
})

globalClickHandlers['js-fill-address-id'] = (node) => {
	var container = node.closest('.js-profile')
	var tid = container.querySelector('.js-type').dataset.tid
	var typeButton = document.querySelector(`#change-data [data-tid="${tid}"]`)
	setTimeout(() => {
		typeButton.click()
	}, 0);
	;[...document.forms].filter(f => f.name == "updateProfile").map(form => {
		form.querySelector("input[name=ID]").value = container.dataset.profileId
			;[...container.querySelectorAll('.js-property')]
				.filter(p => p.style?.display != "none")
				.map(p => {
					if (input = form.querySelector("input[name=\"profileToSave[" + p.dataset.code + "]\"")) {
						input.value = p.querySelector('.js-property-value').innerText.trim()
						if (input.value?.length) {
							input.closest(".form__line").classList.add("_form-focus")
						}
					}
				})
	})
}

async function logout() {
	await fetch(`/?logout=yes&sessid=${window.BX.bitrix_sessid()}`, {
		"body": null,
		"method": "GET",
	});
	window.location = "/"
}

async function deleteProfile(node) {
	let body = new FormData()
	let container = node.closest('.js-profile')
	body.append('sessid', window.BX.bitrix_sessid())
	body.append('profileId', parseInt(container.dataset.profileId))
	let getParams = `?mode=class&c=mkmatrix:profile.detail&action=deleteProfile`

	let res = await fetch(
		"/bitrix/services/main/ajax.php" + getParams,
		{
			method: "POST",
			body,
		}
	).then(r => r.json())

	if (res.status === "error") {
		console.log(res.errors);
	} else {
		container.remove()
	}
}