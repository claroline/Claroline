import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

const UploadModal = (props) =>
  <Modal
    {...omit(props, 'formName', 'uploadDestinations', 'fetchUploadDestinations', 'add', 'upload', 'uploadEnabled', 'workspace')}
    icon="fa fa-fw fa-file"
    title={trans('upload')}
    onEntering={() => {
      if (0 === props.uploadDestinations.length) {
        props.fetchUploadDestinations(props.workspace)
      }
    }}
  >
    <FormData
      level={5}
      name={props.formName}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'parent',
              label: trans('directory'),
              type: 'choice',
              required: true,
              options: {
                choices: props.uploadDestinations.reduce((acc, current) => Object.assign(acc, {[current.id]: current.name}), {}),
                condensed: true
              }
            }, {
              name: 'file',
              label: trans('file'),
              type: 'file',
              required: true
            }
          ]
        }
      ]}
    />

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      label={trans('upload', {}, 'actions')}
      primary={true}
      disabled={!props.uploadEnabled}
      callback={() => {
        props.upload((resourceNode) => {
          props.fadeModal()
          props.add(resourceNode)
        })
      }}
    />
  </Modal>

UploadModal.propTypes = {
  formName: T.string.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  uploadDestinations: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  fetchUploadDestinations: T.func.isRequired,
  uploadEnabled: T.bool.isRequired,
  upload: T.func.isRequired,
  add: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  UploadModal
}
