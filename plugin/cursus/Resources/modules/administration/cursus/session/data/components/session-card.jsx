import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans, displayDate} from '#/main/app/intl'
import {DataCard} from '#/main/app/data/components/card'

import {Session as SessionTypes} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon="fa fa-calendar-week"
    title={props.data.name}
    subtitle={trans('date_range', {
      start: displayDate(props.data.restrictions.dates[0]),
      end: displayDate(props.data.restrictions.dates[1])
    })}
    contentText={props.data.description}
  />

SessionCard.propTypes = {
  data: T.shape(
    SessionTypes.propTypes
  ).isRequired
}

export {
  SessionCard
}
