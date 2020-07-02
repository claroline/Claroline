import React from 'react'
import {PropTypes as T} from 'prop-types'
import {DataCard} from '#/main/app/data/components/card'

import {CursusGroup as CursusGroupType} from '#/plugin/cursus/administration/cursus/prop-types'

const CursusGroupCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-users"
    title={props.data.group.name}
    subtitle={props.data.registrationDate}
  />

CursusGroupCard.propTypes = {
  data: T.shape(CursusGroupType.propTypes).isRequired
}

export {
  CursusGroupCard
}
