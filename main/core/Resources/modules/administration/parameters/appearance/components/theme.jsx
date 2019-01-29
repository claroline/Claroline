import React from 'react'
import get from 'lodash/get'

import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

const Theme = () =>
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
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'meta.plugin',
            label: trans('plugin'),
            type: 'string',
            hideLabel: true,
            render: (theme) => {
              return get(theme, 'meta.plugin')
            }
          }, {
            name: 'name',
            label: trans('name'),
            type: 'string',
            required: true
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            label: trans('description'),
            type: 'string',
            options: {
              long: true
            }
          }, {
            name: 'meta.published',
            label: trans('publish', {}, 'actions'),
            type: 'boolean'
          }
        ]
      }
    ]}
  />

export {
  Theme
}
