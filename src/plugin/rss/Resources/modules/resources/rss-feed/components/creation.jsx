import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/resource/modals/creation/store'

// TODO : try to use <channel> title and description to feed resourceNode props

const RssFeedCreation = () =>
  <FormData
    level={5}
    name={selectors.STORE_NAME}
    dataPart={selectors.FORM_RESOURCE_PART}
    sections={[
      {
        title: trans('url'),
        primary: true,
        fields: [
          {
            name: 'url',
            label: trans('url'),
            type: 'url',
            required: true
          }
        ]
      }
    ]}
  />

export {
  RssFeedCreation
}
