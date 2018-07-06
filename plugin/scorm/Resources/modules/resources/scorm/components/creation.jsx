import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'

const ScormForm = props =>
  <FormContainer
    level={5}
    name={selectors.FORM_NAME}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'file',
            label: trans('file'),
            type: 'file',
            required: true,
            onChange: (data) => props.update(data),
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
  update: T.func.isRequired
}

const ScormCreation = connect(
  state => ({
    workspaceId: selectors.parent(state).workspace.id
  }),
  (dispatch) => ({
    update(data) {
      dispatch(actions.updateResource('hashName', data.hashName))
      dispatch(actions.updateResource('version', data.version))
      dispatch(actions.updateResource('scos', data.scos))
    }
  })
)(ScormForm)

export {
  ScormCreation
}
