import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors as resourceSelectors} from '#/main/core/resource'
import {actions as formActions} from '#/main/app/content/form'

const DropzoneEditorDrop = () => {
  const workspace = useSelector(resourceSelectors.workspace)
  const dropzone = useSelector(editorSelectors.resource)

  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(formActions.updateProp(resourceSelectors.EDITOR_NAME, 'resource.'+prop, value))
  }, [resourceSelectors.EDITOR_NAME])

  return (
    <EditorPage
      title={trans('drop', {}, 'dropzone')}
      dataPart="resource"
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'parameters.dropType',
              type: 'choice',
              label: trans('drop_type', {}, 'dropzone'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DROP_TYPES
              }
            }, {
              name: 'instruction',
              type: 'html',
              label: trans('instructions', {}, 'dropzone'),
              required: true,
              options: {
                workspace: workspace,
                minRows: 3
              }
            }
          ]
        }, {
          title: trans('advanced'),
          primary: true,
          hideTitle: true,
          fields: [
            {
              name: 'parameters.documents',
              label: trans('allowed_document_types', {}, 'dropzone'),
              help: trans('allowed_document_types_info', {}, 'dropzone'),
              type: 'choice',
              required: true,
              options: {
                choices: constants.DOCUMENT_TYPES,
                multiple: true,
                condensed: false,
                inline: false
              }
            }, {
              name: 'parameters.revisionEnabled',
              type: 'boolean',
              label: trans('allow_revision_request', {}, 'dropzone')
            }
          ]
        }
      ]}
    />
  )
}

export {
  DropzoneEditorDrop
}
