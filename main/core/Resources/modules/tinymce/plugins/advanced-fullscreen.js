import tinymce from 'tinymce/tinymce'

//import {trans} from '#/main/core/translation'

function getMenubar(editor) {
  return editor.theme.panel.find('menubar')[0]
}

function getStatusbar(editor) {
  return editor.theme.panel.find('panel').slice(-1)[0]
}

function toggleAdvanced(editor, advancedMode) {
  if (advancedMode) {
    getMenubar(editor).show()
    getStatusbar(editor).show()
  } else {
    getMenubar(editor).hide()
    getStatusbar(editor).hide()
  }
}

// Register new plugin
tinymce.PluginManager.add('advanced-fullscreen', (editor) => {
  const DOMUtils = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils')
  editor.on('BeforeRenderUI', () => {
    toggleAdvanced(editor, false)
  })

  editor.on('FullscreenStateChanged', (e) => {
    toggleAdvanced(editor, e.state)
    DOMUtils.DOM.fire(window, 'resize')
  })

  // provides a toolbar button
  /*editor.addButton('advanced-fullscreen', {
    icon: 'fullscreen',
    tooltip: trans('advanced_tools'),
    onclick: function () {
      this.active(!this.active())
      toggleAdvanced(editor, this.active())

      if (this.active()) {
        DOMUtils.DOM.addClass(document.body, 'mce-fullscreen')
        DOMUtils.DOM.addClass(editor.getContainer(), 'mce-fullscreen')
      } else {
        DOMUtils.DOM.removeClass(document.body, 'mce-fullscreen')
        DOMUtils.DOM.removeClass(editor.getContainer(), 'mce-fullscreen')
      }

      DOMUtils.DOM.fire(window, 'resize')
    }
  })*/
})
