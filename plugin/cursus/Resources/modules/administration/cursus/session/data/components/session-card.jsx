import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/core/data/components/data-card'

import {Session as SessionType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-cubes"
    title={props.data.name}
    subtitle={props.data.meta.course.title}
    contentText={props.data.description}
  />

SessionCard.propTypes = {
  data: T.shape(SessionType.propTypes).isRequired
}

export {
  SessionCard
}
