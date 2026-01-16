// global click events
const globalClickHandlers = {
	"popup__close": () => closeModal()
}


document.addEventListener("click", function (e) {
	var foundNodes = []
	var checkRecursive = (target) => {
		if (target === document || target == undefined) {
			return false
		}
		var cl = target.classList

		if (cl === undefined) {
			return false
		}
		var contains = false
		for (var c of Object.keys(globalClickHandlers)) {
			if (cl.contains(c)) {
				contains = true
				break
			}
		}
		if (contains) {
			foundNodes.push(target)
		}

		return checkRecursive(target.parentElement)
	}
	checkRecursive(e.target)

	var handlers = Object.entries(globalClickHandlers)
	foundNodes.map(node => {
		handlers.map(([className, callback]) => {
			if (node.classList.contains(className)) {
				callback(node)
			}
		})
	})
})
