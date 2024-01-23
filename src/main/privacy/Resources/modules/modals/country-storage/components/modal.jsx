import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {selectors} from '#/main/privacy/administration/privacy/store'

const CountryStorageModal = props =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-earth-americas"
    title={trans('country_storage',{},'privacy')}
    size="lg"
  >
    <FormData
      level={5}
      flush={true}
      name={selectors.FORM_NAME}
      definition={[
        {
          icon: 'fa fa-earth-americas',
          title: trans('country_storage', {}, 'privacy'),
          fields: [
            {
              name: 'countryStorage',
              label: trans('country_storage', {}, 'privacy'),
              type: 'country'
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

CountryStorageModal.propTypes = {
  save: T.func.isRequired,
  saveEnabled: T.bool.isRequired,
  fadeModal: T.func.isRequired,
  formData: T.object.isRequired
}

export {
  CountryStorageModal
}
