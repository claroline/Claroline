/**
 * Created by panos on 10/16/15.
 */
(function() {
    'use strict';

    window.ArrayIterator = function(a) {
        this.arr = a;
    };
    ArrayIterator.__name__ = ["ArrayIterator"];
    ArrayIterator.prototype = {
        hasNext: function() {
            return this.cur < this.arr.length;
        },
        next: function() {
            return this.arr[this.cur++];
        },
        cur: 0,
        arr: [],
        __class__: ArrayIterator
    };
}());