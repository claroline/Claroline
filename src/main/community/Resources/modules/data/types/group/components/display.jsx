import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Group as GroupTypes} from '#/main/community/prop-types'
import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupDisplay = (props) => props.data ?
  <GroupCard
    data={props.data}
    size="xs"
  /> :
  <ContentPlaceholder
    icon="fa fa-users"
    title={trans('no_group')}
  />

GroupDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    GroupTypes.propTypes
  ))
}

export {
  GroupDisplay
}
