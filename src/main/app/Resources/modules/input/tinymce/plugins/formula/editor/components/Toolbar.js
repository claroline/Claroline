import {AreaPanel} from '#/main/app/input/tinymce/plugins/formula/editor/components/AreaPanel'
import {ButtonPanel} from '#/main/app/input/tinymce/plugins/formula/editor/components/ButtonPanel'
import {MlangPanel} from '#/main/app/input/tinymce/plugins/formula/editor/components/MlangPanel'

const Toolbar = function (panels, editor) {
  this.element = document.createElement('div')
  this.element.className = 'fm-editor-toolbar'
  this.editor = editor
  this.createPanels(panels)
}
Toolbar.__name__ = ['Toolbar']
Toolbar.prototype = {
  createPanels: function (panels) {
    this.panels = []
    this.activePanel = null
    var panelDiv = document.createElement('div')
    panelDiv.className = 'fm-editor-panel-container'
    this.createMlangPanel(panelDiv)

    let panelObj
    for (var i = 0; i < panels.length; i++) {
      var panel = panels[i]
      if (panel.visible) {
        panelObj = new AreaPanel(panel, this)
      } else {
        panelObj = new ButtonPanel(panel, this)
      }

      panelDiv.appendChild(panelObj.element)

      this.panels.push(panelObj)
    }
    this.element.appendChild(panelDiv)
  },
  createMlangPanel: function (parentDiv) {
    this.mlangPanel = new MlangPanel(this)
    parentDiv.appendChild(this.mlangPanel.element)
  },
  redraw: function () {
    for (var i = 0; i < this.panels.length; i++) {
      var panel = this.panels[i]
      panel.redraw()
    }
  },
  element: null,
  activePanel: null,
  mlangPanel: null,
  editor: null,
  panels: null,
  __class__: Toolbar
}

export {
  Toolbar
}
