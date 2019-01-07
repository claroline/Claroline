import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Group as GroupType} from '#/main/core/user/prop-types'
import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupsDisplay = (props) => !isEmpty(props.data) ?
  <div>
    {props.data.map(group =>
      <GroupCard
        key={`group-card-${group.id}`}
        data={group}
      />
    )}
  </div> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-users"
    title={trans('no_group')}
  />

GroupsDisplay.propTypes = {
  data: T.arrayOf(T.shape(GroupType.propTypes))
}

export {
  GroupsDisplay
}
