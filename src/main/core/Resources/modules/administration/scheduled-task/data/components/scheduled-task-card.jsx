import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'

const ScheduledTaskCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-clock-o"
    title={props.data.name}
    subtitle={trans(props.data.type)}
    footer={props.data.meta.lastExecution &&
      <span>
        {trans('executed_at')} <b>{displayDate(props.data.meta.lastExecution, false, true)}</b>
      </span>
    }
  />

ScheduledTaskCard.propTypes = {
  data: T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired,
    type: T.string.isRequired,
    meta: T.shape({
      lastExecution: T.string
    }).isRequired
  }).isRequired
}

export {
  ScheduledTaskCard
}
