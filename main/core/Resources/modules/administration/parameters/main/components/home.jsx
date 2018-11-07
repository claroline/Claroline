import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

const Home = () =>
  <FormData
    name="parameters"
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: '/identification',
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-user-plus',
        title: trans('Home'),
        defaultOpened: true,
        fields: [
          {
            name: 'home.redirection_type',
            type: 'choice',
            label: trans('home_redirection_type'),
            options: {
              multiple: false,
              condensed: false,
              choices: {
                new: trans('new'),
                old: trans('old'),
                url: trans('url')
              }
            },
            linked: [{
              name: 'home.redirection_url',
              type: 'string',
              label: trans('url'),
              displayed: (data) => data.home.redirection_type === 'url',
              hideLabel: true
            }]
          }
        ]
      }
    ]}
  />

export {
  Home
}
