import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'

import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const ThermsModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-solid fa-pen-to-square"
    title={trans('Conditions d\'utilisation', {}, 'actions')}
  >
    <FormData
      name={selectors.FORM_NAME}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'tos.enabled',
              type: 'boolean',
              label: trans('terms_of_service_activation_message', {}, 'privacy'),
              help: trans('terms_of_service_activation_help', {}, 'privacy'),
              linked: [
                {
                  name: 'tos.text',
                  type: 'translated',
                  label: trans('terms_of_service', {}, 'privacy'),
                  required: true,
                  displayed: get(props.parameters, 'tos.enabled')
                }
              ]
            }
          ]
        }
      ]}
    >

    </FormData>
  </Modal>

ThermsModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func,
  parameters: T.shape({
    tos: T.shape({
      enabled: T.bool
    })
  })
}

export {
  ThermsModal
}