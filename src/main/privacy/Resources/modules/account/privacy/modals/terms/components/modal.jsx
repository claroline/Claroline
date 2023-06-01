import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'
import {selectors} from '#/main/privacy/account/privacy/modals/terms/store/selectors'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const TermsModal = (props) => {
  console.log('TermsModal props', props)
  return(
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'item')}
      icon="fa fa-fw fa-solid fa-pen-to-square"
      title={trans('terms_of_service', {}, 'privacy')}
    >
      <DetailsData
        name={selectors.FORM_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'item.isTermsOfServiceEnabled',
                type: 'boolean',
                label: trans('terms_of_service_activation_message', {}, 'privacy'),
                help: trans('terms_of_service_activation_help', {}, 'privacy'),
                linked: [
                  {
                    name: 'item.termsOfService',
                    type: 'string',
                    label: trans('terms_of_service', {}, 'privacy'),
                    options: {long: true}
                  }
                ]
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
      </DetailsData>
    </Modal>
  )
}

TermsModal.propTypes = {
  formData: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func,
  item: T.object
}

export {
  TermsModal
}