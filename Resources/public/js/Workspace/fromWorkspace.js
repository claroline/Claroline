$( document ).ready(function() {
	$('button, a').tooltip({placement:'top'});

	$('.path-delete').confirmModal({
		confirmTitle : 'Confirmation',
		confirmMessage : 'Pouvez-vous confirmer l\'action de suppression ?',
		confirmOk : 'OK',
		confirmCancel : 'Annuler',
		confirmDirection : 'ltr',
		confirmStyle : 'primary',
		confirmCallback : submitForm
	});

	function submitForm(target) {
		$(target).parent('form').submit();
	}
});