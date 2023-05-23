import React, { useEffect } from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/modals/country/store/selectors'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const CountryModal = (props) => {

  // Add the effect here
  useEffect(() => {
    console.log('saveEnabled a changé : ', props.saveEnabled)
  }, [props.saveEnabled])

  return (
    <Modal
      {...omit(props, 'formData', 'saveEnabled', 'save')}
      icon="fa fa-fw fa-solid fa-globe"
      title={trans('Pays de stockage', {}, 'actions')}
    >
      <FormData
        name={selectors.FORM_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'privacy.countryStorage',
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
          callback={() => {
            props.save(props.formData, props.fadeModal)
          }}
        />
      </FormData>
    </Modal>
  )
}

CountryModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func
}

export {
  CountryModal
}
