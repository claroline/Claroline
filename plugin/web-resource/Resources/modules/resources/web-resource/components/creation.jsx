import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions, selectors} from '#/main/core/resource/modals/creation/store'

const WebResourceForm = props =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
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
      dispatch(actions.updateResource('size', data.size))
      dispatch(actions.updateResource('hashName', data.hashName))

    }
  })
)(WebResourceForm)

export {
  WebResourceCreation
}
