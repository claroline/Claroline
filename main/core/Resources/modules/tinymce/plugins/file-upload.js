import $ from 'jquery'
import tinymce from 'tinymce/tinymce'
import invariant from 'invariant'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'

const common = window.Claroline.Common
const modal = window.Claroline.Modal
const resourceManager = window.Claroline.ResourceManager

/**
 * Open a directory picker from a TinyMCE editor.
 */
function openDirectoryPicker() {
  if (!resourceManager.hasPicker('tinyMceDirectoryPicker')) {
    resourceManager.createPicker('tinyMceDirectoryPicker', {
      resourceTypes: ['directory'],
      isDirectorySelectionAllowed: true,
      isPickerMultiSelectAllowed: false,
      callback: (nodes) => {
        let val, path
        for (let id in nodes) {
          if (nodes.hasOwnProperty(id)) {
            val = nodes[id][4]
            path = nodes[id][3]
          }
        }

        //file_form_destination
        let html = '<option value="' + val + '">' + path + '</option>'
        $('#file_form_destination').append(html)
        $('#file_form_destination').val(val)
      }
    }, true)
  } else {
    resourceManager.picker('tinyMceDirectoryPicker', 'open')
  }
}

function uploadFile(editor) {
  modal.fromRoute('claro_upload_modal', null, function (element) {
    element
      .on('click', '.filePicker', function () {
        $('#file_form_file').click()
      })
      .on('change', '#file_form_destination', function () {
        if ($('#file_form_destination').val() === 'others') {
          openDirectoryPicker()
        }
      })
      .on('change', '#file_form_file', function () {
        common.uploadfile(
          this,
          element,
          $('#file_form_destination').val(),
          (nodes = {}) => {
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
        )
      })
  })
}

// Register new plugin
tinymce.PluginManager.add('file-upload', (editor) => {
  // provides an insert menu item
  editor.addMenuItem('file-upload', {
    icon: 'file-upload',
    text: trans('file'),
    context: 'insert',
    onclick: () => uploadFile(editor)
  })

  // provides a toolbar button
  editor.addButton('file-upload', {
    icon: 'file-upload',
    tooltip: trans('upload'),
    onclick: () => uploadFile(editor)
  })
})
