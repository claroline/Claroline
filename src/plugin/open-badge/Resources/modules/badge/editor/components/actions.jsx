import React, {useMemo} from 'react'
import {useHistory} from 'react-router-dom'
import {useDispatch, useSelector} from 'react-redux'

import {EditorActions} from '#/main/app/editor'
import {selectors as securitySelectors} from '#/main/app/security'
import {selectors as contextSelectors} from '#/main/app/context'
import {route, selectors as toolSelectors} from '#/main/core/tool'

import {getActions} from '#/plugin/open-badge/badge/utils'
import {actions, selectors} from '#/plugin/open-badge/badge/editor/store'
import {actions as baseActions} from '#/plugin/open-badge/tools/badges/store'

const BadgeEditorActions = () => {
  const dispatch = useDispatch()
  const history = useHistory()

  const currentUser = useSelector(securitySelectors.currentUser)
  const contextPath = useSelector(contextSelectors.path)
  const path = useSelector(toolSelectors.path)
  const badge = useSelector(selectors.data)

  const badgeActions = useMemo(() => getActions([badge], {
    add: () => {
      dispatch(actions.reset(badge.id, true))
      dispatch(baseActions.openBadge(badge.id))
    },
    update: () => {
      dispatch(actions.reset(badge.id, true))
      dispatch(baseActions.openBadge(badge.id))
    },
    delete: () => {
      history.push(route('badges', contextPath))
    }
  }, path, currentUser), [badge.id])

  return (
    <EditorActions
      actions={badgeActions}
    />
  )
}

export {
  BadgeEditorActions
}
