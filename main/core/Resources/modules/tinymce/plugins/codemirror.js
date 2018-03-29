import tinymce from 'tinymce/tinymce'

import {asset} from '#/main/core/scaffolding/asset'

// Most of this is copy-pasted from original plugin only to fix template URL.
// TODO : either make tinymce-codemirror work without this, or implement our own plugin.

function showSourceEditor(editor) {
  // Insert caret marker
  editor.focus()
  editor.selection.collapse(true)
  editor.selection.setContent('<span class="CmCaReT" style="display:none">&#0;</span>')

  // Open editor window
  const win = editor.windowManager.open({
    title: 'HTML source code',
    url: asset('packages/tinymce-codemirror/plugins/codemirror/source.html'),
    width: 800,
    height: 550,
    resizable: true,
    maximizable: true,
    buttons: [
      {
        text: 'Ok',
        subtype: 'primary',
        onclick: function () {
          const doc = document.querySelectorAll('.mce-container-body>iframe')[0]
          doc.contentWindow.submit()
          win.close()
        }
      }, {
        text: 'Cancel',
        onclick: 'close'
      }
    ]
  })
}

// Register new plugin
tinymce.PluginManager.requireLangPack('codemirror') // todo make translations work
tinymce.PluginManager.add('codemirror', function (editor) {
  // Add a button to the button bar
  editor.addButton('code', {
    title: 'Source code',
    icon: 'code',
    onclick: () => showSourceEditor(editor)
  })

  // Add a menu item to the tools menu
  editor.addMenuItem('code', {
    icon: 'code',
    text: 'Source code',
    context: 'tools',
    onclick: () => showSourceEditor(editor)
  })
})
