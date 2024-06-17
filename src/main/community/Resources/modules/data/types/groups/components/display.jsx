import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {Group as GroupType} from '#/main/community/prop-types'
import {GroupCard} from '#/main/community/group/components/card'

const GroupsDisplay = (props) => {
  if (!isEmpty(props.data)) {
    return (
      <>
        {props.data.map(group =>
          <GroupCard
            key={`group-card-${group.id}`}
            data={group}
            size="sm"
          />
        )}
      </>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-users"
      title={trans('no_group')}
    />
  )
}

GroupsDisplay.propTypes = {
  data: T.arrayOf(T.shape(
    GroupType.propTypes
  ))
}

export {
  GroupsDisplay
}
