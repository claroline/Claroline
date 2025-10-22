import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const DpoModal = props =>
  <FormModal
    {...omit(props, 'dpo')}
    icon="fa fa-fw fa-user-shield"
    title={trans('dpo',{},'privacy')}
    name="dpoForm"
    isNew={false}
    target={['apiv2_privacy_update']}
    data={{dpo: props.dpo}}
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

DpoModal.propTypes = {
  dpo: T.shape({
    name: T.string,
    email: T.string,
    address: T.shape({
      street1: T.string,
      street2: T.string,
      postalCode: T.string,
      city: T.string,
      state: T.string,
      country: T.string
    }),
    phone: T.string
  })
}

export {
  DpoModal
}
