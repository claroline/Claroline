import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'

// TODO : should reuse the standard file resource creation

const ScormForm = props =>
  <FormData
    embedded={true}
    level={5}
    name={selectors.STORE_NAME}
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
            required: true,
            onChange: (data) => props.update(props.newNode, data),
            options: {
              uploadUrl: ['apiv2_scorm_archive_upload', {workspace: props.workspaceId}]
            }
          }
        ]
      }
    ]}
  />

ScormForm.propTypes = {
  workspaceId: T.string.isRequired,
  newNode: T.object,
  update: T.func.isRequired
}

const ScormCreation = connect(
  state => ({
    newNode: selectors.newNode(state),
    workspaceId: selectors.newNode(state).workspace.id
  }),
  (dispatch) => ({
    update(newNode, data) {
      dispatch(actions.updateResource('hashName', data.url))
      dispatch(actions.updateResource('version', data.version))
      dispatch(actions.updateResource('scos', data.scos))

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
)(ScormForm)

export {
  ScormCreation
}
