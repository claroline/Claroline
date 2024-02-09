import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'

const WebResourceForm = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    embedded={true}
    dataPart={selectors.FORM_RESOURCE_PART}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'file',
            label: trans('file'),
            type: 'file',
            help: trans('has_to_be_a_zip', {}, 'resource'),
            required: true,
            onChange: (data) => props.update(props.newNode, data),
            options: {
              uploadUrl: ['apiv2_webresource_file_upload', {workspace: props.workspaceId}]
            }
          }
        ]
      }
    ]}
  />

WebResourceForm.propTypes = {
  update: T.func.isRequired,
  newNode: T.object,
  workspaceId: T.string.isRequired
}

const WebResourceCreation = connect(
  state => ({
    newNode: selectors.newNode(state),
    workspaceId: selectors.newNode(state).workspace.id
  }),
  (dispatch) => ({
    update(newNode, data) {
      // update resource props
      dispatch(actions.updateResource('size', data.size))
      dispatch(actions.updateResource('hashName', data.url))

      // update node props
      let cleanedName = data.name.replace('_', ' ').substring(0, data.name.lastIndexOf('.'))
      cleanedName = cleanedName.charAt(0).toUpperCase() + cleanedName.slice(1)
      if (!newNode.name) {
        // only set name if none provided
        dispatch(actions.updateNode('name', cleanedName))
      }

      if (!newNode.code) {
        // only set code if none provided
        dispatch(actions.updateNode('code', cleanedName))
      }

    }
  })
)(WebResourceForm)

export {
  WebResourceCreation
}
