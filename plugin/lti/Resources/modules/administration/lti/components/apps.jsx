import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {getPlainText} from '#/main/app/data/types/html/utils'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {DataCard} from '#/main/app/content/card/components/data'

const Apps = () =>
  <ListData
    name="lti.apps"
    fetch={{
      url: ['apiv2_lti_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/form/${row.id}`
    })}
    delete={{
      url: ['apiv2_lti_delete_bulk']
    }}
    definition={[
      {
        name: 'title',
        label: trans('title'),
        type: 'string',
        primary: true,
        displayed: true
      }, {
        name: 'url',
        label: trans('url', {}, 'lti'),
        type: 'string',
        displayed: true
      }, {
        name: 'description',
        label: trans('description'),
        type: 'html',
        displayed: true
      }
    ]}

    card={(row) =>
      <DataCard
        icon='fa fa-plug'
        title={row.data.title}
        subtitle={row.data.url}
        contentText={getPlainText(row.data.description)}
      />
    }
  />

export {
  Apps
}
