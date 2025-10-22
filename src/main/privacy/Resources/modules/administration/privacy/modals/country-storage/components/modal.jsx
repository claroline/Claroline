import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const CountryStorageModal = props =>
  <FormModal
    {...omit(props, 'countryStorage')}
    name="countryStorageForm"
    icon="fa fa-earth"
    title={trans('country_storage',{},'privacy')}
    isNew={false}
    target={['apiv2_privacy_update']}
    data={{countryStorage: props.countryStorage}}
    definition={[
      {
        title: trans('general'),
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

CountryStorageModal.propTypes = {
  countryStorage: T.string
}

export {
  CountryStorageModal
}
