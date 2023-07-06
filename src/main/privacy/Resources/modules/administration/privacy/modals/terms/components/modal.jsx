import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/modals/terms/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const TermsModal = (props) => {
  const handleFormSubmit = () => {
    props.save(props.formData, (termsOfService, termsOfServiceEnabled) => {
      props.fadeModal()
      if (props.onSave) {
        props.onSave(termsOfService, termsOfServiceEnabled)
      }
    })
  }

  return(
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'termsOfService', 'termsOfServiceEnabled', 'onSave')}
      icon="fa fa-fw fa-solid fa-pen-to-square"
      title={trans('terms_of_service', {}, 'privacy')}
      onEntering={() => props.reset(props.termsOfService, props.termsOfServiceEnabled)}
    >
      <FormData
        name={selectors.STORE_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'termsOfServiceEnabled',
                type: 'boolean',
                label: trans('terms_of_service_activation_message', {}, 'privacy'),
                linked: [
                  {
                    name: 'termsOfService',
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
          callback={handleFormSubmit}
        />
      </FormData>
    </Modal>
  )
}


TermsModal.propTypes = {
  formData: T.object.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  onSave: T.func,
  fadeModal: T.func,
  termsOfServiceEnabled: T.bool.isRequired,
  termsOfService: T.string.isRequired,
  reset: T.func.isRequired
}

export {
  TermsModal
}
