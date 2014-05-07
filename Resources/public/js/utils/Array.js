// Removes an element from an array.
// String value: the value to search and remove.
// return: an array with the removed element; false otherwise.
Array.prototype.remove = function(value) {
    var index = this.indexOf(value);
    if (index != -1) {
        return this.splice(index, 1); // The second parameter is the number of elements to remove.
    }
    return false;
}