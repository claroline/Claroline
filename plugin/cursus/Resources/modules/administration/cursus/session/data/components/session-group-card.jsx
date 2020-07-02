import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/data/components/card'

import {SessionGroup as SessionGroupType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionGroupCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-users"
    title={props.data.group.name}
    subtitle={props.data.registrationDate}
  />

SessionGroupCard.propTypes = {
  data: T.shape(SessionGroupType.propTypes).isRequired
}

export {
  SessionGroupCard
}
