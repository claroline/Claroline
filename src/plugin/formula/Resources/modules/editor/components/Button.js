const Button = function (button, section) {
  this.id = button
  this.element = document.createElement('span')
  this.element.className = 'fm-editor-button math math-' + button
  this.element.id = this.id + '-btn'
  this.section = section
  this.createEvent()

  var obj = this
  this.element.addEventListener('click', function (event) {
    obj.addEquationEvent.clientX = event.clientX
    obj.addEquationEvent.clientY = event.clientY
    obj.element.dispatchEvent(obj.addEquationEvent)
  })
}
Button.__name__ = ['Button']
Button.prototype = {
  createEvent: function () {
    this.addEquationEvent = document.createEvent('Event')
    this.addEquationEvent.formulaAction = this.id
    this.addEquationEvent.initEvent('addEquation', true, true)
  },
  element: null,
  addEquationEvent: null,
  id: null,
  section: null,
  __class__: Button
}

export {
  Button
}
