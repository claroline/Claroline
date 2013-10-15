function TruncateTextFilter() {
    return function (text, length, end) {
        var truncated = '';
        if (undefined !== text && null !== text && 0 !== text.length) {
            // Default length for truncate : 20 caracters.
            if (isNaN(length))
                length = 20;

            if (end === undefined)
                end = "...";

            if (text.length <= length || text.length - end.length <= length) {
                truncated = text;
            }
            else {
                truncated = String(text).substring(0, length-end.length) + end;
            }
        }
        
        return truncated;
    };
}