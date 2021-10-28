import React from 'react'
import {PropTypes as T} from 'prop-types'
import moment from 'moment'

import classes from 'classnames'
import {constants as constList} from '#/main/app/content/list/constants'
import {constants} from '#/plugin/cursus/constants'
import {displayDate, now} from '#/main/app/intl/date'
import {trans} from '#/main/app/intl/translation'
import {MODAL_SUBSCRIPTION_STATUS} from '#/plugin/cursus/subscription/modals/status'
import {MODAL_BUTTON, CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_SUBSCRIPTION_ABOUT} from '#/plugin/cursus/subscription/modals/about'
import {ListData} from '#/main/app/content/list/containers/data'
import {
  Quota as QuotaTypes,
  Statistics as StatisticsTypes
} from '#/plugin/cursus/prop-types'

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
    actions={(rows) => !props.isAdmin && rows[0].status != 0 ? [
      {
        name: 'nothing',
        type: CALLBACK_BUTTON,
        icon: 'fas fa-fw fa-ban',
        label: trans('nothing', {}, 'actions'),
        callback: () => {}
      }
    ] : [
      {
        name: 'edit',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit', {}, 'actions'),
        modal: [MODAL_SUBSCRIPTION_STATUS, {
          canValidate: props.statistics.calculated + rows[0].session.quotas.days <= props.quota.threshold,
          status: !props.isAdmin && rows[0].status != 0 ? [] : (
            rows[0].session.quotas.used && props.quota.useQuotas ?
              [0, 1, 2, 3] :
              rows[0].session.restrictions.dates[0] >= now() ?
                [0, 1, 2] :
                [0, 1]
          ).filter(status => status != rows[0].status),
          changeStatus: (status, remark) => props.setSubscriptionStatus(props.quota.id, rows[0].id, status, remark)
        }]
      }
    ]}
    definition={[
      {
        name: 'user.name',
        alias: 'user',
        type: 'string',
        label: trans('user'),
        displayed: true,
        filterable: false,
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
        name: 'session.quotas.days',
        alias: 'days',
        type: 'number',
        label: trans('days'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'session.pricing.price',
        alias: 'price',
        type: 'currency',
        label: trans('price'),
        displayed: true,
        filterable: false,
        sortable: false
      }, {
        name: 'session.restrictions.dates[0]',
        alias: 'start_date',
        type: 'choice',
        label: trans('start_date'),
        displayed: true,
        sortable: false,
        options: {
          choices: new Array(Number(moment().utc().local().format('YYYY')) - 2019).fill(0).reduce((accum, none, delta) => {
            accum[`${2021 + delta}`] = `${2021 + delta}`
            return accum
          }, {})
        },
        render: (row) => displayDate(row.session.restrictions.dates[0])
      }, {
        name: 'status',
        type: 'choice',
        label: trans('status'),
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
  ),
  statistics: T.shape(
    StatisticsTypes.propTypes
  ).isRequired,
  isAdmin: T.bool.isRequired
}

export {
  SubscriptionAll
}
