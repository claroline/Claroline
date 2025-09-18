import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {DashboardActions} from '#/main/app/dashboard/components/actions'
import {hasPermission, selectors as securitySelectors} from '#/main/app/security'
import {selectors as contextSelectors} from '#/main/app/context'

import {getActions} from '#/main/core/tool/utils'
import {selectors} from '#/main/core/tool/store'

const ToolDashboardActions = () => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const contextPath = useSelector(contextSelectors.path)

  const tool = useSelector(selectors.tool)

  const toolActions = useMemo(() => {
    if (!isEmpty(tool)) {
      return getActions([tool], {}, contextPath, currentUser)
    }

    return []
  }, [tool])

  return (
    <DashboardActions
      canAdministrate={hasPermission('administrate', tool)}
      actions={toolActions}
    />
  )
}

export {
  ToolDashboardActions
}
