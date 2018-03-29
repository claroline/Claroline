import React from 'react'
import {PropTypes as T} from 'prop-types'

import {DataCard} from '#/main/core/data/components/data-card'

import {enumRole} from '#/main/core/user/role/constants'
import {Role as RoleTypes} from '#/main/core/user/prop-types'

const RoleCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-id-badge"
    title={props.data.name}
    subtitle={enumRole[props.data.meta.type]}
  />

RoleCard.propTypes = {
  data: T.shape(
    RoleTypes.propTypes
  ).isRequired
}

export {
  RoleCard
}
