import tinymce from 'tinymce/tinymce'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'

const buildFrame = editor => {
  let currentNode = editor.selection.getNode()
  let params = {lang: editor.settings.language || 'en'}
  if (currentNode.nodeName.toLowerCase() === 'img' && currentNode.className.indexOf('fm-editor-equation') > -1) {
    if (currentNode.getAttribute('data-mlang')) params.mlang = currentNode.getAttribute('data-mlang')
    if (currentNode.getAttribute('data-equation')) params.equation = currentNode.getAttribute('data-equation')
  }
  
  return `<iframe name="tinymceFormula" id="tinymceFormula" src="${url(['icap_formula_plugin_index', params])}" scrolling="yes"></iframe>`
}

const showFormulaDialog = editor => {
  const win = editor.windowManager.open({
    title: trans('formula', {}, 'formula'),
    width: 900,
    height: 510,
    name: 'tinymceFormula',
    id: 'tinymceFormula',
    html: buildFrame(editor),
    buttons: [
      {
        text: trans('cancel', {}, 'actions'),
        onclick: function () {
          win.close()
        }
      },
      
      {
        text: trans('insert_formula', {}, 'formula'),
        subtype: 'primary',
        onclick: function () {
          if (window.frames['tinymceFormula'] && window.frames['tinymceFormula'].getData) {
            window.frames['tinymceFormula'].getData(function (src, mlang, equation) {
              if (src) {
                editor.insertContent('<img class="fm-editor-equation" src="' + src + '" data-mlang="' + mlang + '" data-equation="' + encodeURIComponent(equation) + '"/>')
              }
              win.close()
            })
          } else {
            win.close()
          }
        }
      }
    ]
  })
}

tinymce.PluginManager.add('formula', function (editor) {
  editor.addButton('formula', {
    icon: 'percent',
    tooltip: trans('formula', {}, 'formula'),
    onclick: () => showFormulaDialog(editor),
    onPostRender: function () {
      let _this = this   // reference to the button itself
      editor.on('NodeChange', e => {
        _this.active(e.element.className.indexOf('fm-editor-equation') > -1 && e.element.nodeName.toLowerCase() === 'img')
      })
    }
  })
})