import React, {useMemo} from 'react'
import {PropTypes as T} from 'prop-types'

import {useReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {constants as listConst} from '#/main/app/content/list/constants'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {User as UserTypes} from '#/main/community/user/prop-types'

const ProfileWorkspaces = (props) => {
  const listName = 'profileWorkspaces'
  // append list reducer to the store if not already mounted
  const reducer = useMemo(() => makeListReducer(listName, {
    sortBy: {property: 'name', direction: 1}
  }), [listName])
  useReducer(listName, reducer)

  return (
    <WorkspaceList
      className="mt-4 mb-5"
      url={['apiv2_workspace_list_registered', {userId: props.user.id}]}
      name={listName}
      display={{
        current: listConst.DISPLAY_LIST
      }}
      actions={undefined}
    />
  )
}

ProfileWorkspaces.propTypes = {
  user: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  ProfileWorkspaces
}
