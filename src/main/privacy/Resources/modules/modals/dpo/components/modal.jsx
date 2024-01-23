import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {selectors} from '#/main/privacy/administration/privacy/store'

const DpoModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-user-shield"
    title={trans('dpo',{},'privacy')}
    size="lg"
  >
    <FormData
      level={5}
      flush={true}
      name={selectors.FORM_NAME}
      definition={[
        {
          title: trans('dpo', {}, 'privacy'),
          fields: [
            {
              name: 'dpo.name',
              label: trans('name'),
              type: 'string'
            }, {
              name: 'dpo.email',
              label: trans('email'),
              type: 'email'
            }, {
              name: 'dpo.phone',
              label: trans('phone'),
              type: 'string'
            }, {
              name: 'dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }
      ]}
    />
    <Button
      className="modal-btn"
      variant="btn"
      size="lg"
      type={CALLBACK_BUTTON}
      label={trans('save', {}, 'actions')}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
      primary={true}
    />
  </Modal>

DpoModal.propTypes = {
  save: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  fadeModal: T.func.isRequired,
  formData: T.object.isRequired
}

export {
  DpoModal
}
