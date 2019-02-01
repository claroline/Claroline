import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {AssertionList} from '#/plugin/open-badge/tools/badges/assertion/components/assertion-user-list'

import {withRouter} from '#/main/app/router'

// todo : restore custom actions the same way resource actions are implemented
const ProfileBadgeList = props => {
  const id = props.match.params.id

  return (
    <ListData
      name="badges.mine"
      fetch={{
        url: ['apiv2_assertion_user_list', {user: id}],
        autoload: true
      }}
      definition={AssertionList.definition}
      primaryAction={AssertionList.open}
      card={AssertionList.card}
      display={{current: listConstants.DISPLAY_LIST_SM}}
    />
  )
}

const RoutedProfileBadgeList = withRouter(ProfileBadgeList)

export {
  RoutedProfileBadgeList as ProfileBadgeList
}
