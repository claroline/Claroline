import React from 'react'

import {ListData} from '#/main/app/content/list/containers/data'
import issue from '#/plugin/open-badge/tools/badges/badge/actions/issue'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

import {AssertionList} from '#/plugin/open-badge/tools/badges/assertion/components/assertion-user-list'
// todo : restore custom actions the same way resource actions are implemented
export const MyBadges = () => {
  return(
    <ListData
      name={selectors.STORE_NAME + '.badges.mine'}
      fetch={{
        url: ['apiv2_assertion_current_user_list'],
        autoload: true
      }}
      primaryAction={AssertionList.open}
      definition={AssertionList.definition}
      actions={(rows) => [issue(rows)]}
      card={AssertionList.card}
    />
  )
}
