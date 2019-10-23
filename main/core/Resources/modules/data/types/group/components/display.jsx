import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

import {Group as GroupType} from '#/main/core/user/prop-types'
import {GroupCard} from '#/main/core/user/data/components/group-card'

const GroupDisplay = (props) => !isEmpty(props.data) ?
  <Fragment>
    {props.data.map(group =>
      <GroupCard
        key={`group-card-${group.id}`}
        data={group}
        size="xs"
      />
    )}
  </Fragment> :
  <EmptyPlaceholder
    icon="fa fa-users"
    title={trans('no_group')}
  />

GroupDisplay.propTypes = {
  data: T.arrayOf(T.shape(GroupType.propTypes))
}

export {
  GroupDisplay
}
