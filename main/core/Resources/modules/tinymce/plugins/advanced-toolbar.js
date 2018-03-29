import tinymce from 'tinymce/tinymce'

import {trans} from '#/main/core/translation'

/**
 * Finds the advanced toolbar in the rendered theme.
 *
 * @param editor
 */
function getAdvancedToolbar(editor) {
  return editor.theme.panel.find('toolbar').slice(1)
}

/**
 * Shows/Hides the advanced toolbar.
 *
 * @param btn
 * @param editor
 */
function toggleToolbar(btn, editor) {
  btn.active(!btn.active())
  if (btn.active()) {
    getAdvancedToolbar(editor).show()
  } else {
    getAdvancedToolbar(editor).hide()
  }
}

// Register new plugin
tinymce.PluginManager.add('advanced-toolbar', (editor) => {
  const DOMUtils = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils')

  editor.on('BeforeRenderUI', function () {
    getAdvancedToolbar(editor).hide()
  })

  // provides a toolbar button
  editor.addButton('advanced-toolbar', {
    icon: 'advanced-toolbar',
    tooltip: trans('advanced_tools'),
    onclick: function () {
      toggleToolbar(this, editor)

      DOMUtils.DOM.fire(window, 'resize')
    }
  })
})
