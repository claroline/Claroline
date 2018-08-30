import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/core/translation'
import {CompositeIcon} from '#/main/app/icon/font'
import {DataCard} from '#/main/core/data/components/data-card'

import {constants} from '#/main/core/user/role/constants'
import {Role as RoleTypes} from '#/main/core/user/prop-types'

const RoleCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={
      <CompositeIcon
        primary="id-badge"
        secondary={classes({
          globe: constants.ROLE_PLATFORM === props.data.type,
          book: constants.ROLE_WORKSPACE === props.data.type,
          asterisk: constants.ROLE_CUSTOM === props.data.type,
          user: constants.ROLE_USER === props.data.type
        })}
      />
    }
    title={trans(props.data.translationKey)}
    subtitle={props.data.name}
  />

RoleCard.propTypes = {
  data: T.shape(
    RoleTypes.propTypes
  ).isRequired
}

export {
  RoleCard
}
