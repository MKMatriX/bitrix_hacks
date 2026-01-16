function openModal(selector) {
	let p = window.popup
	p.close()
	setTimeout(() => {
		p.targetOpen.selector = selector
		p._selectorOpen = true;
		p.open();
	}, 600);
}

function closeModal(selector) {
	window.popup.close(selector)
}