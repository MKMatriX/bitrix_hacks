(function(w){
	if ("undefined" !== typeof w.mkCallback) {
		return;
	}

	var cb = function (args) {
		this.init(args)
		if (this.errors.length) {
			console.log("callback error");
			console.error(this.errors);
		}
	}

	cb.prototype = {
		id: "",
		params: {},
		// form: false,
		data: {},
		// state: {},
		signedParamsString: "",
		fileuploadUrl: "/local/components/mkmatrix/callback/fileupload.php",
		additionalParams: {FROM_PAGE: w.location.pathname},
		YA_COUNTER: 0,
		YA_GOAL: "",
		// spy: function() {},
		onSuccess: function () {
			// openModal('#thanks') // эту функцию еще нужно прописать, я обычно в каждом проекте делаю такие функции, и плевать что использовал верстальщик
			this.form.reset()
		},
		onError: function(errors) {
			for (key in errors) {
				console.log(errors[key]);
			}
		},
		validate: function(form) {
			return true
			// return $(form).parsley().isValid();
		},
		beforeSend: function() {},
		afterSend: function() {},

		fileuploadParams: {dataType: 'json'},
		errors: [],
	}

	cb.prototype.parseParams = function(type, name, needed, strong) {
		if ("undefined" === typeof this.params[name]) {
			if (needed) {
				this.errors.push("Need " + name + " of type " + type + " in params")
			}
			return false
		}

		if (type !== typeof this.params[name]) {
			this.errors.push(
				name + " type error, expected " + type +
					" have " + typeof this.params[name] +
					" = " + this.params[name]
			)
			return false
		}

		if (strong) {
			switch (type) {
				case "string" :
					if (this.params[name].length == 0) {
						this.errors.push(name + " is empty")
						return false
					}
					break;
				case "number" :
					if (this.params[name] > 0) {
						this.errors.push(name + " = 0")
						return false
					}
					break;
				case "object" :
					if ("object" !== typeof this.params[name]) {
						this.errors.push(name + " is no object")
						return false
					}
					break
				default:
					break;
			}
		}

		this[name] = this.params[name]
		return true;
	}

	cb.prototype.init = function(params) {
		this.params = params

		this.parseParams('string', 'signedParamsString', true, true)
		this.parseParams('string', 'url')
		this.parseParams('string', 'fileuploadUrl')

		this.parseParams('string', 'YA_GOAL')
		this.parseParams('string', 'YA_COUNTER')
		this.YA_COUNTER = parseInt(this.YA_COUNTER)

		this.parseParams('function', 'onSuccess')
		this.parseParams('function', 'onError')
		this.parseParams('function', 'validate')

		this.parseParams('object', 'fileuploadParams')

		if (this.parseParams('string', 'id', true)) {
			this.form = document.querySelector('#' + this.params.id)
			if (this.form === null) {
				this.errors.push("can't find form #" + this.params.id)
				return;
			}
		}

		this.ajaxForm = new AjaxForm(this.form)

		this.ajaxForm.onSuccess = (...args) => {
			AjaxForm.prototype.onSuccess.apply(this.ajaxForm, args)
			this.onSuccess(...args)
		}
		this.ajaxForm.onError = (...args) => {
			AjaxForm.prototype.onError.apply(this.ajaxForm, args)
			this.onError(...args)
		}
		this.ajaxForm.onBeforeSend = (...args) => {
			AjaxForm.prototype.onBeforeSend.apply(this.ajaxForm, args)
			this.beforeSend()
			this.ajaxForm.body = this.body
		}
		this.ajaxForm.onAfterSend = (...args) => {
			AjaxForm.prototype.onAfterSend.apply(this.ajaxForm, args)
			this.afterSend(...args)
		}

		this.initFiles()

		var _ = this;

		if (!this.errors.length) {
			this.form.addEventListener('submit', function(e) {
				e.preventDefault()
				_.gatherData()
				if (_.validate(_.form, _.body, _) === true) {
					_.send()
				}
			})
		} else {
			console.log('form ' + (this.id || '') + ' error!');
			this.errors.map(console.error)
		}

		// console.log("callback inited");
	};

	cb.prototype.initFiles = function () {
		if ("undefined" === typeof $.fn.fileupload) {
			return false
		}

		this.filesInputs = this.form.querySelectorAll("input[type=file]")

		if (this.filesInputs.length) {
			w.$(this.filesInputs)
				.attr('data-url', this.fileuploadUrl)
				.fileupload(this.fileuploadParams)
		}
	}

	cb.prototype.gatherData = function() {
		var additionalParams = this.additionalParams
		if ("function" === typeof this.additionalParams) {
			additionalParams = this.additionalParams()
		}

		this.body = new FormData(this.form)
		this.body.append('sessid', w.BX.bitrix_sessid())
		this.body.append('signedParameters', this.signedParamsString)

		Object.entries(this.additionalParams).map(([k,v]) => this.body.append(k,v))
	};

	cb.prototype.send = async function() {
		var _ = this;

		if (this.errors.length) {
			console.log(this.form[0]);
			console.log("can't send with errors");
			console.error(this.errors);
			return false
		}

		var data = await _.ajaxForm.submit()

		if (data.status !== "error") {
			_.spy();
		}
	};

	cb.prototype.spy = function() {
		if (this.YA_COUNTER > 0 && this.YA_GOAL.length > 0) {
			try {
				w["yaCounter"+this.YA_COUNTER].reachGoal(this.YA_GOAL)
			} catch(error) {
				alert("ya metrika error! Проверьте введенные параметры");
				console.error(error);
			}
		}
	}

	w.mkCallback = cb
})(window)