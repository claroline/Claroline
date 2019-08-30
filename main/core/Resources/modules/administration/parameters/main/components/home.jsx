import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {route as adminRoute} from '#/main/core/administration/routing'
import {selectors} from '#/main/core/administration/parameters/main/store'
import {constants} from '#/main/app/layout/sections/home/constants'

const Home = () =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: adminRoute('main_settings'),
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        fields: [
          {
            name: 'home.type',
            type: 'choice',
            label: trans('type'),
            required: true,
            options: {
              multiple: false,
              condensed: true,
              choices: constants.HOME_TYPES
            },
            linked: [
              {
                name: 'home.data',
                type: 'url',
                label: trans('url'),
                required: true,
                displayed: (data) => constants.HOME_TYPE_URL === data.home.type
              }, {
                name: 'home.data',
                type: 'html',
                label: trans('content'),
                required: true,
                displayed: (data) => constants.HOME_TYPE_HTML === data.home.type
              }
            ]
          }
        ]
      }
    ]}
  />

export {
  Home
}
