//import tinymce from 'tinymce/tinymce'

import {trans} from '#/main/app/intl/translation'

const openFileDialog = (editor) => {
  editor.windowManager.open({
    title: 'Upload file',
    body: {
      type: 'panel',
      items: [
        {
          type: 'selectbox',
          name: 'destination',
          label: trans('directory'),
          //enabled: false,
          items: [
            { value: 'one', text: 'One' },
            { value: 'two', text: 'Two' }
          ]
        }, {
          type: 'dropzone',
          name: 'file',
          label: trans('file')
        }
      ]
    },
    buttons: [
      {
        type: 'cancel',
        text: 'Close',
        buttonType: 'secondary'
      }, {
        type: 'submit',
        text: 'Save',
        buttonType: 'primary'
      }
    ],
    onChange: (api, evt) => {
      if ('file' === evt.name) {
        const data = api.getData()
        console.log(data.file)
      }
    },
    onSubmit: (api) => {
      api.close()
    },
    onCancel: (api) => api.close()
  })
}

// Register new plugin
window.tinymce.PluginManager.add('file', (editor) => {
  editor.options.register('workspace', {
    processor: 'object'
  })

  const workspace = editor.options.get('workspace')

  // provides an insert menu item
  editor.ui.registry.addMenuItem('file', {
    icon: 'new-document',
    text: 'File...',
    onAction: () => openFileDialog(editor, workspace)
  })

  // provides a toolbar button
  editor.ui.registry.addButton('file', {
    icon: 'new-document',
    tooltip: 'Upload file',
    onAction: () => openFileDialog(editor, workspace)
  })
})
