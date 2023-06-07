//import tinymce from 'tinymce/tinymce'

import {theme} from '#/main/app/config/theme'

import {Editor} from '#/plugin/formula/editor'

const isFormula = (node) => node.className.indexOf('fm-editor-equation') > -1 && node.nodeName.toLowerCase() === 'img'

const showFormulaDialog = (editor) => {
  let currentNode = editor.selection.getNode()
  let params = {lang: editor.options.get('language') || 'en'}
  if (currentNode && isFormula(currentNode)) {
    if (currentNode.getAttribute('data-mlang')) {
      params.mlang = currentNode.getAttribute('data-mlang')
    }
    if (currentNode.getAttribute('data-equation')) {
      params.equation = currentNode.getAttribute('data-equation')
    }
  }

  const formulaEditor = new Editor(params)

  const win = editor.windowManager.open({
    title: 'Insert/Edit equation',
    size: 'large',
    body: {
      type: 'panel',
      items: [
        {
          type: 'htmlpanel',
          html: `<div id="fm-editor-body">
                    <link rel="stylesheet" type="text/css" href="${theme('claroline-distribution-plugin-formula-formula-editor')}" />
                </div>`
        }
      ]
    },
    buttons: [
      {
        text: 'Cancel',
        type: 'cancel',
        buttonType: 'secondary'
      }, {
        text: 'Save',
        type: 'submit',
        buttonType: 'primary'
      }
    ],
    onCancel: function (api) {
      api.close()
    },
    onSubmit: function (api) {
      formulaEditor.getEquationImage((src, mlang, equation) => {
        if (src) {
          const image = new Image()
          image.src = src
          image.className = 'fm-editor-equation'
          image.dataset.mlang = mlang
          image.dataset.equation = encodeURIComponent(equation)

          if (currentNode && isFormula(currentNode)) {
            image.width = currentNode.width
            image.height = currentNode.height
          }

          editor.insertContent(image.outerHTML.toString())
        }
      })

      api.close()
    }
  })

  formulaEditor.init()

  return win
}

window.tinymce.PluginManager.add('formula', function (editor) {
  const onSetupFormula = (buttonApi) => {
    const node = editor.selection.getNode()
    if (node) {
      buttonApi.setActive(node.className.indexOf('fm-editor-equation') > -1 && node.nodeName.toLowerCase() === 'img')
    }

    return editor.selection.selectorChangedWithUnbind('img.fm-editor-equation', buttonApi.setActive).unbind
  }

  // provide ui icon
  editor.ui.registry.addIcon('formula', '<svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 576 512"><!--! Font Awesome Free 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. --><path d="M289 24.2C292.5 10 305.3 0 320 0H544c17.7 0 32 14.3 32 32s-14.3 32-32 32H345L239 487.8c-3.2 13-14.2 22.6-27.6 24s-26.1-5.5-32.1-17.5L76.2 288H32c-17.7 0-32-14.3-32-32s14.3-32 32-32H96c12.1 0 23.2 6.8 28.6 17.7l73.3 146.6L289 24.2zM393.4 233.4c12.5-12.5 32.8-12.5 45.3 0L480 274.7l41.4-41.4c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3L525.3 320l41.4 41.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L480 365.3l-41.4 41.4c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L434.7 320l-41.4-41.4c-12.5-12.5-12.5-32.8 0-45.3z"/></svg>')

  // provides an insert menu item
  editor.ui.registry.addMenuItem('formula', {
    icon: 'formula',
    text: 'Equation...',
    //onSetup: onSetupFormula,
    onAction: () => showFormulaDialog(editor)
  })

  // provides a toolbar button
  editor.ui.registry.addToggleButton('formula', {
    icon: 'formula',
    tooltip: 'Insert/Edit equation',
    onSetup: onSetupFormula,
    onAction: () => showFormulaDialog(editor)
  })
})
