import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'

const CountryModal = (props) =>
  <Modal
    {...omit(props, 'formData', 'saveEnabled', 'save')}
    icon="fa fa-fw fa-solid fa-pen-to-square"
    title={trans('Pays de stockage des données', {}, 'privacy')}
  >
    <FormData
      name={selectors.STORE_NAME}
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

    </FormData>
  </Modal>

CountryModal.propTypes = {
  formData: T.object,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func
}

export {
  CountryModal
}