var utilitiesApp = angular.module('utilitiesApp', []);

utilitiesApp.factory('UtilityFunctions', function(){
	//Dynamic deep get for a JavaScript object
	var deepGetValue = function(){
		var o = object;
		path = path.replace(/\[(\w+)\]/g, '.$1');
		path = path.replace(/^\./, '');
		var a = path.split('.');
		while (a.length) {
			var n = a.shift();
			if (n in o) {
				o = o[n];
			} else {
				return;
			}
		}
		return o;
	}

	//Dynamic deep set for a JavaScript object
	var deepSetValue = function(object, path, value) {
		var a = path.split('.');
		var o = object;
		for (var i = 0; i < a.length - 1; i++) {
			var n = a[i];
			if (n in o) {
				o = o[n];
			} else {
				o[n] = {};
				o = o[n];
			}
		}
		o[a[a.length - 1]] = value;
	}
	
	//Test if value is defined and not null
	var isDefinedNotNull = function(value) {
		return angular.isDefined(value)&&value!=null;
	}
	
	return {
		'deepGetValue' : deepGetValue,
		'deepSetValue' : deepSetValue,
		'isDefinedNotNull' : isDefinedNotNull
	}
});