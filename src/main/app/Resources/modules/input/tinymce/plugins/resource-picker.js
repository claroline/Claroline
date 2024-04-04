//import tinymce from 'tinymce/tinymce'
import invariant from 'invariant'

import {makeId} from '#/main/core/scaffolding/id'
import {url} from '#/main/app/api'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

import {MODAL_RESOURCES} from '#/main/core/modals/resources'

// TODO : remove placeholder on selection cancel

/**
 * Opens a resource picker from a TinyMCE editor.
 */
function openResourcePicker(editor) {
  const showModal = editor.options.get('showModal')

  // We need to generate an anchor in the content to know where to put the resource we will pick.
  // For now, the resource picker will unmount the TinyMCE editor when shown in a modal,
  // so we will lose the cursor position.
  const placeholder = `<span id="resource-picker-${makeId()}" style="display: none;">${trans('resource')}</span>`
  editor.insertContent(placeholder)

  showModal(MODAL_RESOURCES, {
    selectAction: (selected) => ({
      type: CALLBACK_BUTTON,
      label: trans('select', {}, 'actions'),
      callback: () => {
        editor.setProgressState(true)

        selected.map((resourceNode, index) => {
          fetch(
            url(['claro_resource_embed', {id: resourceNode.id}]), {
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

                const placeholderPosition = content.indexOf(placeholder)
                if (-1 !== placeholderPosition) {
                  // append resource
                  content = content.substr(0, placeholderPosition) + responseText + content.substr(placeholderPosition)

                  if (1 === selected.length || index + 1 === selected.length) {
                    // only one selected resource or appending the last one, we need to remove the placeholder
                    content = content.replace(placeholder, '')
                    initiator.setProgressState(false)
                  }

                  // replace content in editor
                  initiator.setContent(content)
                }

                initiator.fire('change')
              }
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
        })
      }
    })
  })
}

// Register new plugin
window.tinymce.PluginManager.add('resource-picker', (editor) => {
  editor.options.register('showModal', {
    processor: 'function'
  })

  // provide ui icon
  editor.ui.registry.addIcon('resource-picker', '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 512 512"><!--! Font Awesome Free 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. --><path d="M447.1 96h-172.1L226.7 50.75C214.7 38.74 198.5 32 181.5 32H63.1c-35.35 0-64 28.66-64 64v320c0 35.34 28.65 64 64 64h384c35.35 0 64-28.66 64-64V160C511.1 124.7 483.3 96 447.1 96zM463.1 416c0 8.824-7.178 16-16 16h-384c-8.822 0-16-7.176-16-16V96c0-8.824 7.178-16 16-16h117.5c4.273 0 8.293 1.664 11.31 4.688L255.1 144h192c8.822 0 16 7.176 16 16V416z"/></svg>')

  // provides an insert menu item
  editor.ui.registry.addMenuItem('resource-picker', {
    icon: 'resource-picker',
    text: 'Resource...',
    //context: 'insert',
    onAction: () => openResourcePicker(editor)
  })

  // provides a toolbar button
  editor.ui.registry.addButton('resource-picker', {
    icon: 'resource-picker',
    tooltip: 'Insert resource',
    onAction: () => openResourcePicker(editor)
  })
})
