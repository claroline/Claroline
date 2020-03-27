import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'

import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {RoleCard} from '#/main/core/user/data/components/role-card'

const RoleDisplay = (props) => props.data ?
  <RoleCard
    data={props.data}
    size="xs"
  /> :
  <EmptyPlaceholder
    icon="fa fa-id-card"
    title={trans('no_role')}
  />

RoleDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    RoleTypes.propTypes
  ))
}

export {
  RoleDisplay
}
