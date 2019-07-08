import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const Domain = () =>
  <FormData
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('internet'),
        defaultOpened: true,
        fields: [
          {
            name: 'internet.domain_name',
            type: 'string',
            label: trans('domain_name'),
            required: false,
            linked: [
              {
                name: 'ssl.enabled',
                type: 'boolean',
                label: trans('ssl_enabled'),
                required: false
              }, {
                name: 'ssl.version',
                type: 'string',
                label: trans('version'),
                displayed: (parameters) => parameters.ssl.enabled
              }
            ]
          }, {
            name: 'internet.google_meta_tag',
            type: 'string',
            label: trans('google_tag_validation'),
            required: false
          }
        ]
      }
    ]}
  />

export {
  Domain
}
