import React from 'react'
import {PropTypes as T} from 'prop-types'

import classes from 'classnames'
import {constants} from '#/plugin/cursus/constants'
import {getSubscriptionStatus} from '#/plugin/cursus/utils'
import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {SubscriptionCard} from '#/plugin/cursus/subscription/components/card'

const SubscriptionAll = (props) =>
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
        name: 'session.course.name',
        alias: 'course',
        type: 'string',
        label: trans('course', {}, 'cursus'),
        displayed: true,
        primary: true,
        sortable: false
      }, {
        name: 'session.name',
        alias: 'session',
        type: 'string',
        label: trans('session', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'date',
        alias: 'date',
        type: 'date',
        label: trans('registred', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status'),
        displayed: true,
        options: {
          choices: constants.SUBSCRIPTION_STATUSES
        },
        render: (row) => (
          <span className={classes('label', `label-${getSubscriptionStatus(row)}`)}>
            {getSubscriptionStatus(row)}
          </span>
        )
      }
    ]}
    card={SubscriptionCard}
  />

SubscriptionAll.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired
}

export {
  SubscriptionAll
}
