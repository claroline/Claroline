import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CallbackButton} from '#/main/app/buttons/callback'
import {FormData} from '#/main/app/content/form/containers/data'

import {trans} from '#/main/app/intl/translation'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {selectors} from '#/main/core/resources/file/modals/form/store'

// TODO : display old file

const FileFormModal = props =>
  <Modal
    {...omit(props, 'resourceNode', 'data', 'saveEnabled', 'resetForm', 'save', 'onChange')}
    icon="fa fa-fw fa-exchange-alt"
    title={trans('change_file', {}, 'resource')}
    onEntering={() => props.resetForm()}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'file',
              type: 'file',
              label: trans('file'),
              required: true
            }
          ]
        }
      ]}
    />

    <CallbackButton
      className="modal-btn btn"
      disabled={!props.saveEnabled || !props.data.file}
      callback={() => {
        props.save(props.resourceNode, props.data.file, props.onChange)
        props.fadeModal()
      }}
      primary={true}
    >
      {trans('save', {}, 'actions')}
    </CallbackButton>
  </Modal>

FileFormModal.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  data: T.shape({
    file: T.object
  }).isRequired,
  saveEnabled: T.bool.isRequired,
  resetForm: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired,
  onChange: T.func
}

export {
  FileFormModal
}
