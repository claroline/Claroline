//translator class
var Translator = function () {
	this.translations = {
		'fr': {
			'backup_title': 'Sauvegarde',
			'backup_content': 'Vous devriez faire une sauvegarde',
			'pre_update_title': 'Script d\'avant mise à jour',
			'pre_update_content': 'Veuillez exécuter le script de pré mise à jour',
			'replace_vendor_title': 'Remplacement des vendors',
			'replace_vendor_content': 'Veuillez remplacer le dossier vendor',
			'post_update_title': 'Script de fin de mise à jour',
			'post_update': 'Veuillez exécuter le script de fin de mise à jour'
		},
		'en': {
			'backup_title': 'Backup',
			'backup_content': 'You should do a backup',
			'pre_update_title': 'Pre update script',
			'pre_update_content': 'Please execute the pre update script',
			'replace_vendor_title': 'Vendors replacement',
			'replace_vendor_content': 'Please replace the vendors',
			'post_update_title': 'Post update script',
			'post_update_content': 'Please execute the post update script'
		}
	};
	this.locale = 'en';
};

Translator.prototype.translate = function(translationKey) {
	return this.translations[this.locale][translationKey];
};

Translator.prototype.setLocale = function(locale) {
	this.locale = locale;
};

/********************************************************************/

//logDisplayer class
var LogDisplayer   = function(logWindow) {
	this.logWindow = $(logWindow);
	this.logFile   = undefined;
	this.url       = undefined;
};

LogDisplayer.prototype.pollStatus = function () {
	$.ajax(this.url).done((function (data) {
		this.logWindow.html(data);
		//this.logWindow.animate({ scrollTop: this.logWindow[0].scrollHeight });
	}).bind(this));
};

LogDisplayer.prototype.setLogFile = function(logFile) {
	this.logFile = logFile;
	//maybe this shouldn't be done here but w/e
	this.url     = this.logWindow.attr('data-url') + '?logFile=' + logFile;
}

LogDisplayer.prototype.start = function() {
	this.pollStatus();
	this.refresh = setInterval((function () {
		this.pollStatus();
	}).bind(this), 2000);
};

LogDisplayer.prototype.stop = function () {
	clearInterval(this.refresh);
}
