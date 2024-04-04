//import tinymce from 'tinymce/tinymce'
import invariant from 'invariant'

import {makeId} from '#/main/core/scaffolding/id'
import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'

import {MODAL_TINYMCE_UPLOAD} from '#/main/app/input/tinymce/modals/upload'

// TODO : remove placeholder on upload cancel

/**
 * Open a file upload form from a TinyMCE editor.
 */
function openFileUpload(editor) {
  const showModal = editor.options.get('showModal')

  // We need to generate an anchor in the content to know where to put the file we will upload.
  // For now, the resource picker will unmount the TinyMCE editor when shown in a modal,
  // so we will lose the cursor position.
  const placeholder = `<span id="file-upload-${makeId()}" style="display: none;">${trans('file')}</span>`
  editor.insertContent(placeholder)

  showModal(MODAL_TINYMCE_UPLOAD, {
    workspace: editor.options.get('workspace'),
    add: (newResourceNode) => {
      editor.setProgressState(true)
      fetch(
        url(['claro_resource_embed', {id: newResourceNode.id}]), {
          credentials: 'include'
        })
        .then(response => {
          if (response.ok) {
            return response.text()
          }
        })
        // HACK
        // There is a problem when tinymce is embedded in a modal as the upload modal will replace
        // the current modal which will make the tinyMce editor object to be destroyed.
        // The original modal is reopened automatically when this one is closed and a new tinyMce object is available when the field
        // is re-rendered. But there is no way to know when it's down from here.
        .then(responseText => setTimeout(() => {
          // retrieve the editor which have requested the picker
          // ATTENTION : we don't reuse instance from func params because it could have been removed
          // when tinyMCE is rendered in a modal
          const initiator = window.tinymce.activeEditor || window.tinymce.get(editor.id)
          if (initiator) {
            let content = initiator.getContent()
            content = content.replace(placeholder, responseText)

            // replace content in editor
            initiator.setContent(content)
            initiator.setProgressState(false)
          }

          initiator.fire('change')
        }, 200))
        .catch((error) => {
          // creates log error
          invariant(false, error.message)

          const initiator = window.tinymce.activeEditor || window.tinymce.get(editor.id)
          if (initiator) {
            // displays generic error in ui
            initiator.notificationManager.open({type: 'error', text: trans('error_occurred')})
            initiator.setProgressState(false)
          }
        })
    }
  })
}

// Register new plugin
window.tinymce.PluginManager.add('file', (editor) => {
  editor.options.register('workspace', {
    processor: 'object'
  })
  editor.options.register('showModal', {
    processor: 'function'
  })

  // provides an insert menu item
  editor.ui.registry.addMenuItem('file', {
    icon: 'new-document',
    text: 'File...',
    onAction: () => openFileUpload(editor)
  })

  // provides a toolbar button
  editor.ui.registry.addButton('file', {
    icon: 'new-document',
    tooltip: 'Upload file',
    onAction: () => openFileUpload(editor)
  })
})
