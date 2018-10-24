import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/app/content/card/components/data'

import {Group as GroupTypes} from '#/main/core/user/prop-types'

const GroupCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-users"
    title={props.data.name}
  />

GroupCard.propTypes = {
  data: T.shape(
    GroupTypes.propTypes
  ).isRequired
}

export {
  GroupCard
}
