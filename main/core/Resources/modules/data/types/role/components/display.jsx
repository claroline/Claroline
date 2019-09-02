import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Role as RoleType} from '#/main/core/user/prop-types'
import {RoleCard} from '#/main/core/user/data/components/role-card'

const RoleDisplay = (props) => !isEmpty(props.data) ?
  <Fragment>
    {props.data.map(role =>
      <RoleCard
        key={`group-card-${role.id}`}
        data={role}
        size="sm"
        orientation="col"
      />
    )}
  </Fragment> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-id-card"
    title={trans('no_role')}
  />

RoleDisplay.propTypes = {
  data: T.arrayOf(T.shape(RoleType.propTypes))
}

export {
  RoleDisplay
}
