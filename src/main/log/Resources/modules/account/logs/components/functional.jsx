import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {ContentTitle} from '#/main/app/content/components/title'

import {selectors} from '#/main/log/account/logs/store'

const FunctionalLogList = () =>
  <Fragment>
    <ContentTitle
      title={trans('functional', {}, 'log')}
    />
    <ListData
      name={selectors.FUNCTIONAL_LIST_NAME}
      fetch={{
        url: ['apiv2_logs_functional_list_current'],
        autoload: true
      }}
      definition={[
        {
          name: 'date',
          label: trans('date'),
          type: 'date',
          options: {time: true},
          displayed: true
        }, {
          name: 'details',
          type: 'string',
          label: trans('description'),
          displayed: true
        }, {
          name: 'resource',
          type: 'resource',
          label: trans('resource'),
          displayed: true
        }, {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayed: true
        }, {
          name: 'event',
          type: 'translation',
          label: trans('event'),
          displayed: false,
          options: {
            domain: 'security'
          }
        }
      ]}
    />
  </Fragment>

export {
  FunctionalLogList
}
