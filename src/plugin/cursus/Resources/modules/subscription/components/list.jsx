import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {SubscriptionCard} from '#/plugin/cursus/subscription/components/card'

const SubscriptionList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: `${props.path}/${row.id}`
    })}
    definition={[
      {
        name: 'organization.name',
        alias: 'organization',
        type: 'string',
        label: trans('organization'),
        displayed: true,
        primary: true,
        sortable: false
      }, {
        name: 'validated',
        type: 'number',
        label: trans('validated'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'threshold',
        type: 'number',
        label: trans('threshold'),
        displayed: true,
        filterable: false,
        sortable: false
      }
    ]}
    card={SubscriptionCard}
  />

SubscriptionList.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array])
}

SubscriptionList.defaultProps = {
  url: ['apiv2_cursus_quota_list']
}

export {
  SubscriptionList
}
