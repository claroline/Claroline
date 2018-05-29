import tinymce from 'tinymce/tinymce'
import invariant from 'invariant'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'

const resourceManager = window.Claroline.ResourceManager

/**
 * Opens a resource picker from a TinyMCE editor.
 */
function openResourcePicker(editor) {
  if (!resourceManager.hasPicker('tinyMcePicker')) {
    resourceManager.createPicker('tinyMcePicker', {
      isPickerMultiSelectAllowed: false,
      callback: (nodes = {}) => {
        // embed resourceNode
        const nodeId = Object.keys(nodes)[0]
        const mimeType = nodes[nodeId][2] !== '' ? nodes[nodeId][2] : 'unknown/mimetype'

        const typeParts = mimeType.split('/')

        fetch(
          url(['claro_resource_embed', {node: nodeId, type: typeParts[0], extension: typeParts[1]}]),
          {
            credentials: 'include'
          }
        )
          .then(response => {
            if (response.ok) {
              return response.text()
            }
          })
          .then(responseText => editor.insertContent(responseText))
          .catch((error) => {
            // creates log error
            invariant(false, error.message)
            // displays generic error in ui
            editor.notificationManager.open({type: 'error', text: trans('error_occured')})
          })
      }
    }, true)
  } else {
    resourceManager.picker('tinyMcePicker', 'open')
  }
}

// Register new plugin
tinymce.PluginManager.add('resource-picker', (editor) => {
  // provides an insert menu item
  editor.addMenuItem('resource-picker', {
    icon: 'resource-picker',
    text: trans('resource'),
    context: 'insert',
    onclick: () => openResourcePicker(editor)
  })

  // provides a toolbar button
  editor.addButton('resource-picker', {
    icon: 'resource-picker',
    tooltip: trans('resource'),
    onclick: () => openResourcePicker(editor)
  })
})
