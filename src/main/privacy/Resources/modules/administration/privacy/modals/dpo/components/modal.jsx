import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import get from 'lodash/get'

const DpoModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-solid fa-pen-to-square"
    title={trans('Infos DPO', {}, 'actions')}
  >
    <FormData
      name={`${selectors.FORM_NAME}`}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'privacy.dpo.name',
              label: trans('name'),
              type: 'string'
            },
            {
              name: 'privacy.dpo.email',
              label: trans('email'),
              type: 'email'
            },
            {
              name: 'privacy.dpo.phone',
              label: trans('phone'),
              type: 'string'
            },
            {
              name: 'privacy.dpo.address',
              label: trans('address'),
              type: 'address'
            }
          ]
        }
      ]}
    >
    </FormData>
  </Modal>

DpoModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func,
  parameters: T.shape({
    privacy: T.shape({
      dpo: T.shape({
        name: T.string,
        email: T.string,
        phone: T.string
      })
    }),
    tos: T.shape({
      enabled: T.bool
    })
  })
}

export {
  DpoModal
}