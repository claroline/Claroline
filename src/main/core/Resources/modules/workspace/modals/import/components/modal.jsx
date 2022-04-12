import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/workspace/modals/import/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const ImportModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save', 'reset')}
    icon="fa fa-fw fa-upload"
    title={trans('import')}
    onExiting={() => props.reset()}
  >
    <FormData
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'archive',
              type: 'file',
              label: trans('archive'),
              required: true,
              options: {
                autoUpload: false
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-info',
          title: trans('information'),
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name')
            }, {
              name: 'code',
              type: 'string',
              label: trans('code')
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        htmlType="submit"
        primary={true}
        label={trans('save', {}, 'actions')}
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.formData).then(() => {
            props.fadeModal()
          })
        }}
      />
    </FormData>
  </Modal>

ImportModal.propTypes = {
  saveEnabled: T.bool.isRequired,
  formData: T.object,

  save: T.func.isRequired,
  reset: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  ImportModal
}
