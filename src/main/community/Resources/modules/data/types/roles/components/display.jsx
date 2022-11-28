import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

import {Role as RoleType} from '#/main/community/prop-types'
import {RoleCard} from '#/main/community/role/components/card'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const RolesDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <Fragment>
        {props.data.map(role =>
          <RoleCard
            key={`role-card-${role.id}`}
            data={role}
            size="xs"
          />
        )}
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-id-badge"
      title={trans('no_role')}
    />
  )
}

RolesDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    RoleType.propTypes
  ))
}

export {
  RolesDisplay
}
