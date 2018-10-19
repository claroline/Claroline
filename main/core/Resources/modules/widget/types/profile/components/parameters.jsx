import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

const ProfileWidgetParameters = (props) =>
  <FormData
    embedded={true}
    level={5}
    name={props.name}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'user',
            label: trans('content'),
            type: 'users',
            //should have a "single" mode
            required: true,
            options: {
              minimal: false
            }
          },
          {
            name: 'currentUser',
            type: 'boolean',
            label: trans('current_user')
          }
          
        ]
      }
    ]}
  />
export {
  ProfileWidgetParameters
}
