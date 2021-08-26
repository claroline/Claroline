import React from 'react'
import {PropTypes as T} from 'prop-types'

import classes from 'classnames'
import {constants as constList} from '#/main/app/content/list/constants'
import {constants} from '#/plugin/cursus/constants'
import {trans} from '#/main/app/intl/translation'
import {MODAL_SUBSCRIPTION_STATUS} from '#/plugin/cursus/subscription/modals/status'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_SUBSCRIPTION_ABOUT} from '#/plugin/cursus/subscription/modals/about'
import {ListData} from '#/main/app/content/list/containers/data'
import {Quota as QuotaTypes} from '#/plugin/cursus/prop-types'

import {SubscriptionCard} from '#/plugin/cursus/subscription/components/card'

const SubscriptionAll = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={(row) => ({
      name: 'about',
      type: MODAL_BUTTON,
      icon: 'fa fa-fw fa-info',
      label: trans('show-info', {}, 'actions'),
      modal: [MODAL_SUBSCRIPTION_ABOUT, {
        subscription: row
      }],
      scope: ['object']
    })}
    actions={(rows) => [
      {
        name: 'edit',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        modal: [MODAL_SUBSCRIPTION_STATUS, {
          changeStatus: (status) => {
            props.setSubscriptionStatus(props.quota.id, rows[0].id, status)
          }
        }]
      }
    ]}
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
        name: 'user.name',
        alias: 'user',
        type: 'string',
        label: trans('user', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'session.quotas.days',
        alias: 'days',
        type: 'number',
        label: trans('days', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'session.pricing.price',
        alias: 'price',
        type: 'number',
        label: trans('price', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'session.restrictions.dates[0]',
        alias: 'start_date',
        type: 'date',
        label: trans('start_date', {}, 'cursus'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status', {}, 'cursus'),
        displayed: true,
        sortable: false,
        options: {
          choices: constants.SUBSCRIPTION_STATUSES
        },
        render: (row) => (
          <span className={classes('label', `label-${constants.SUBSCRIPTION_STATUS_COLORS[row.status]}`)}>
            {constants.SUBSCRIPTION_STATUSES[row.status]}
          </span>
        )
      }
    ]}
    card={SubscriptionCard}
    display={{
      available: [
        constList.DISPLAY_TABLE,
        constList.DISPLAY_TABLE_SM
      ],
      current: constList.DISPLAY_TABLE
    }}
    selectable={false}
  />

SubscriptionAll.propTypes = {
  path: T.string.isRequired,
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  setSubscriptionStatus: T.func.isRequired,
  quota: T.shape(
    QuotaTypes.propTypes
  )
}

export {
  SubscriptionAll
}
