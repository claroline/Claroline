import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors} from '#/main/log/account/logs/store'
import {ContentTitle} from '#/main/app/content/components/title'

const SecurityLogList = () =>
  <Fragment>
    <ContentTitle
      title={trans('security', {}, 'log')}
    />
    <ListData
      name={selectors.SECURITY_LIST_NAME}
      fetch={{
        url: ['apiv2_logs_security_list_current'],
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
          name: 'target',
          type: 'user',
          label: trans('target'),
          displayed: false
        }, {
          name: 'event',
          type: 'translation',
          label: trans('event'),
          displayed: false,
          options: {
            domain: 'security'
          }
        }, {
          name: 'doer_ip',
          label: trans('ip_address'),
          type: 'ip',
          displayed: false
        }
      ]}
    />
  </Fragment>

export {
  SecurityLogList
}
