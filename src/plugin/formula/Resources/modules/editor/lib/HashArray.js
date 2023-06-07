import {ArrayIterator} from '#/plugin/formula/editor/lib/ArrayIterator'

const HashArray = function () {
  this.obj = {}
}
HashArray.__name__ = ['HashArray']
HashArray.prototype = {
  toString: function () {
    var str = ''
    str += '{'
    var it = this.keys()
    while (it.hasNext()) {
      var i = it.next()
      str += i
      str += ' => '
      str += this.get(i)
      if (it.hasNext()) str += ', '
    }
    
    str += '}'
    return str
  },
  iterator: function () {
    return {
      ref: this.obj,
      it: this.keys(),
      hasNext: function () {
        return this.it.hasNext()
      },
      next: function () {
        var i = this.it.next()
        return this.ref['$' + i]
      }
    }
  },
  keys: function () {
    var a = []
    for (var vParameter in this.obj) {
      if (this.obj[vParameter]) {
        a.push(vParameter.substr(1))
      }
    }
    
    return ArrayIterator(a)
  },
  remove: function (vParameter) {
    vParameter = '$' + vParameter
    if (!this.obj[vParameter]) {
      return false
    }

    delete(this.obj[vParameter])

    return true
  },
  exists: function (vParameter) {
    return !!this.obj['$' + vParameter]
  },
  get: function (vParameter) {
    return this.obj['$' + vParameter]
  },
  set: function (vParameter, value) {
    this.obj['$' + vParameter] = value
  },
  obj: null,
  __class__: HashArray
}

export {
  HashArray
}
