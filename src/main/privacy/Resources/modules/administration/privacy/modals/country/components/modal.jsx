import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/modals/country/store/selectors'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const CountryModal = props => {

  const handleFormSubmit = () => {
    props.save(props.formData, (countryStorage) => {
      props.fadeModal()
      if (props.onSave) {
        props.onSave(countryStorage)
      }
    })
  }

  return(
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save', 'reset', 'countryStorage', 'onSave')}
      icon="fa fa-fw fa-solid fa-globe"
      title={trans('country_storage', {}, 'privacy')}
      onEntering={() => props.reset(props.countryStorage)}

    >
      <FormData
        name={selectors.STORE_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'countryStorage',
                label: trans('country_storage', {}, 'privacy'),
                type: 'country'
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


CountryModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  onSave: T.func,
  reset: T.func.isRequired,
  fadeModal: T.func,
  countryStorage: T.string
}

export {
  CountryModal
}
