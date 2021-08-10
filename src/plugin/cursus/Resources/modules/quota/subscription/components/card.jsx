import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Subscription as SubscriptionTypes} from '#/plugin/cursus/prop-types'

const SubscriptionCard = props =>
  <DataCard
    {...props}
    className="subscription"
    id={props.data.id}
    poster={null}
    icon="fa fa-graduation-cap"
    title={props.data.organization.name}
    subtitle={trans('threshold') + ' ' + props.data.threshold}
  />

SubscriptionCard.propTypes = {
  data: T.shape(
    SubscriptionTypes.propTypes
  ).isRequired
}

export {
  SubscriptionCard
}
