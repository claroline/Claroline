import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/modals/dpo/store/selectors'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const DpoModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-solid fa-pen-to-square"
    title={trans('dpo_info', {}, 'privacy')}
  >
    <FormData
      name={selectors.STORE_NAME}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'dpo.name',
              label: trans('name'),
              type: 'string'
            },
            {
              name: 'dpo.email',
              label: trans('email'),
              type: 'email'
            },
            {
              name: 'dpo.phone',
              label: trans('phone'),
              type: 'string'
            },
            {
              name: 'dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }
      ]}
    >
      <Button
        className="modal-btn btn"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        htmlType="submit"
        disabled={!props.saveEnabled}
        callback={() => {
          props.save(props.formData, props.fadeModal)
        }}
      />
    </FormData>
  </Modal>

DpoModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func,
  reset: T.func.isRequired,
}

export {
  DpoModal
}