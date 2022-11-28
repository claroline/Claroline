import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {constants} from '#/main/community/constants'
import {Role as RoleTypes} from '#/main/community/role/prop-types'

const RoleCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon={classes('fa fa-fw', {
      'fa-globe': constants.ROLE_PLATFORM === props.data.type,
      'fa-book': constants.ROLE_WORKSPACE === props.data.type,
      'fa-user': constants.ROLE_USER === props.data.type
    })}
    title={trans(props.data.translationKey)}
    subtitle={props.data.name}
    contentText={get(props.data, 'meta.description')}
  />

RoleCard.propTypes = {
  data: T.shape(
    RoleTypes.propTypes
  ).isRequired
}

export {
  RoleCard
}
