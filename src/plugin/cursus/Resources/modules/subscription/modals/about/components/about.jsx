import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {DetailsData} from '#/main/app/content/details/components/data'

import classes from 'classnames'
import {constants} from '#/plugin/cursus/constants'
import {getSubscriptionStatus} from '#/plugin/cursus/utils'
import {Subscription as SubscriptionTypes} from '#/plugin/cursus/prop-types'

const SubscriptionModal = props =>
  <Modal
    {...omit(props, 'event')}
    icon="fa fa-fw fa-info"
    title={trans('about')}
    subtitle={props.subscription.name}
  >
    <DetailsData
      meta={true}
      data={props.subscription}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'user',
              label: trans('user'),
              type: 'user'
            }, {
              name: 'session',
              label: trans('session', {}, 'cursus'),
              type: 'training_session'
            }, {
              name: 'status',
              type: 'choice',
              label: trans('status'),
              options: {
                choices: constants.SUBSCRIPTION_STATUSES
              },
              render: (row) => (
                <span className={classes('label', `label-${constants.SUBSCRIPTION_STATUS_COLORS[getSubscriptionStatus(row)]}`)}>
                  {getSubscriptionStatus(row)}
                </span>
              )
            }
          ]
        }
      ]}
    />
  </Modal>

SubscriptionModal.propTypes = {
  subscription: T.shape(
    SubscriptionTypes.propTypes
  ).isRequired
}

export {
  SubscriptionModal
}
