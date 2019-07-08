import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const Pdf = () =>
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
        title: trans('PDF'),
        defaultOpened: true,
        fields: [
          {
            name: 'pdf.active',
            type: 'boolean',
            label: trans('activated'),
            required: true
          }
        ]
      }
    ]}
  />

export {
  Pdf
}
