import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {QuotaCard} from '#/plugin/cursus/quota/components/card'

const QuotaList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    delete={{
      url: ['apiv2_cursus_quota_delete_bulk'],
      displayed: () => true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: `${props.path}/${row.id}`
    })}
    definition={[
      {
        name: 'organization',
        alias: 'organization',
        type: 'organization',
        label: trans('organization'),
        displayed: true,
        primary: true,
        sortable: false
      }, {
        name: 'threshold',
        type: 'number',
        label: trans('threshold', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }
    ]}
    card={QuotaCard}
    selectable={false}
  />

QuotaList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array])
}

QuotaList.defaultProps = {
  url: ['apiv2_cursus_quota_list']
}

export {
  QuotaList
}
