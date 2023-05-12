import React from 'react'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '../store'

import {trans} from '#/main/app/intl'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

const PrivacyTool = (props) => {
  return(
    <>

      <h1>HELLO !</h1>
      <FormData
        level={2}
        name={selectors.STORE_NAME}
        target={['apiv2_privacy_update']}
        buttons={true}
        cancel={{
          type: LINK_BUTTON,
          target: props.path,
          exact: true
        }}
        locked={props.lockedParameters}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'privacy.countryStorage',
                label: trans('Pays de stockage des données'),
                type: 'country'
              }
            ]
          }
          , {
            icon: 'fa fa-fw fa-user-shield',
            title: trans('dpo'),
            fields: [
              {
                name: 'privacy.dpo.name',
                label: trans('name'),
                type: 'string'
              }, {
                name: 'privacy.dpo.email',
                label: trans('email'),
                type: 'email'
              }, {
                name: 'privacy.dpo.phone',
                label: trans('phone'),
                type: 'string'
              }, {
                name: 'privacy.dpo.address',
                label: trans('address'),
                type: 'address'
              }
            ]
          }, {
            icon: 'fa fa-fw fa-copyright',
            title: trans('terms_of_service'),
            fields: [
              {
                name: 'tos.enabled',
                type: 'boolean',
                label: trans('terms_of_service_activation_message'),
                help: trans('terms_of_service_activation_help'),
                linked: [
                  {
                    name: 'tos.text',
                    type: 'translated',
                    label: trans('terms_of_service'),
                    required: true,
                    displayed: get(props.parameters, 'tos.enabled')
                  }
                ]
              }
            ]
          }
        ]}
      />

    </>
  )
}

export {
  PrivacyTool
}
