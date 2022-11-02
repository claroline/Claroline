import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {DetailsData} from '#/main/app/content/details/containers/data'

import {selectors as baseSelectors} from '#/main/community/tools/community/store'

const Group = () =>
  <DetailsData
    level={3}
    name={`${baseSelectors.STORE_NAME}.groups.current`}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }
    ]}
  />

export {
  Group
}
