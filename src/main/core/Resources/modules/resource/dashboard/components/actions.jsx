import React, {useMemo} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {hasPermission, selectors as securitySelectors} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool'
import {DashboardActions} from '#/main/app/dashboard/components/actions'

import {getActions} from '#/main/core/resource/utils'
import {selectors, actions} from '#/main/core/resource/store'

const ResourceDashboardActions = () => {
  const dispatch = useDispatch()

  const currentUser = useSelector(securitySelectors.currentUser)
  const toolPath = useSelector(toolSelectors.path)
  const resource = useSelector(selectors.resourceNode)

  const resourceActions = useMemo(() => {
    if (!isEmpty(resource)) {
      return getActions([resource], {
        update: () => {
          dispatch(actions.reload())
        }
      }, toolPath, currentUser)
    }

    return []
  }, [resource])

  return (
    <DashboardActions
      canAdministrate={hasPermission('administrate', resource)}
      actions={resourceActions}
    />
  )
}

export {
  ResourceDashboardActions
}
