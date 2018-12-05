import React from 'react'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Javascripts = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/main',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-file',
        title: trans('javascripts'),
        fields: [{
          name: 'javascripts',
          label: trans('javascripts'),
          type: 'collection',
          options: {
            type: 'file',
            placeholder: trans('no_javascript'),
            button: trans('add_javascript')
          }
        }]
      }
    ]}
  />

export {
  Javascripts
}
