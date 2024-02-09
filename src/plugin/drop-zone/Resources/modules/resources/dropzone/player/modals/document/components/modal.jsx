import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/player/modals/document/store'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const AddDocumentModal = (props) =>
  <Modal
    {...omit(props, 'type', 'data', 'saveEnabled', 'save', 'resetForm')}
    icon="fa fa-fw fa-plus"
    title={trans('new_document', {}, 'dropzone')}
    onEntering={() => props.resetForm({type: props.type})}
  >
    <FormData
      flush={true}
      name={selectors.STORE_NAME}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'type',
              type: 'type',
              label: trans('type'),
              hideLabel: true,
              calculated: () => ({
                icon: <span className={constants.DOCUMENT_TYPE_ICONS[props.type]} />,
                name: constants.DOCUMENT_TYPES[props.type],
                description: trans(`document_${props.type}_desc`, {}, 'dropzone')
              })
            }, {
              name: 'data',
              type: props.type,
              label: trans('document', {}, 'dropzone'),
              required: true,
              options: {
                autoUpload: false, // for file
                minRows: 10 // for html
              }
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn"
        variant="btn"
        size="lg"
        type={CALLBACK_BUTTON}
        htmlType="submit"
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.data)
          props.fadeModal()
        }}
      />
    </FormData>
  </Modal>

AddDocumentModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  data: T.object,
  type: T.string.isRequired,
  save: T.func.isRequired,
  resetForm: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  AddDocumentModal
}
