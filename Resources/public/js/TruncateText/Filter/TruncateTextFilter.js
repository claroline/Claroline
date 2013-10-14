function TruncateTextFilter() {
	return function (text, length, end) {
		// Default length for truncate : 20 caracters.
	    if (isNaN(length))
	        length = 20;

	    if (end === undefined)
	        end = "...";

	    if (text.length <= length || text.length - end.length <= length) {
	        return text;
	    }
	    else {
	        return String(text).substring(0, length-end.length) + end;
	    }

	};
}