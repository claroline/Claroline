import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormContainer} from '#/main/core/data/form/containers/form'
import {actions as creationActions, selectors} from '#/main/core/resource/modals/creation/store'


const WebResourceForm = props =>
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
            help: trans('has_to_be_a_zip', {}, 'resource'),
            required: true,
            onChange: (data) => props.update(data),
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
  workspaceId: T.string.isRequired
}

const WebResourceCreation = connect(
  state => ({
    workspaceId: selectors.newNode(state).workspace.id
  }),
  (dispatch) => ({
    update(data) {
      // update resource props
      dispatch(creationActions.updateResource('size', data.size))
      dispatch(creationActions.updateResource('hashName', data.hashName))

    }
  })
)(WebResourceForm)

export {
  WebResourceCreation
}
