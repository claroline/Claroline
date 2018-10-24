import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

import {SessionEvent as SessionEventType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionEventCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-clock-o"
    title={props.data.name}
    subtitle={props.data.meta.session.name}
    contentText={props.data.description}
  />

SessionEventCard.propTypes = {
  data: T.shape(SessionEventType.propTypes).isRequired
}

export {
  SessionEventCard
}
