import React, {useMemo} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {EditorActions} from '#/main/app/editor'
import {selectors as securitySelectors} from '#/main/app/security'

import {getActions} from '#/main/app/context/utils'
import {selectors as contextSelectors} from '#/main/app/context/store'
import {selectors, actions} from '#/main/app/context/editor/store'

const ContextEditorActions = () => {
  const dispatch = useDispatch()
  const history = useHistory()

  const currentUser = useSelector(securitySelectors.currentUser)
  const contextName = useSelector(contextSelectors.type)
  const contextPath = useSelector(contextSelectors.path)

  const contextData = useSelector(selectors.contextData)
  const contextTools = useSelector(selectors.enabledTools)

  const refresher = {
    update: (contexts) => {
      // checks if the action has modified the current context
      const currentContext = contexts.find(context => context.id === get(contextData, 'id'))
      if (currentContext) {
        dispatch(actions.reload(currentContext, contextTools))
      }
    },
    delete: (contexts) => {
      // checks if the action has deleted the current node
      const currentContext = contexts.find(c => c.id === get(contextData, 'id'))
      if (currentContext) {
        history.push('/')
      }
    }
  }

  const contextActions = useMemo(() => {
    if (!isEmpty(contextData)) {
      return getActions(contextName, [contextData], refresher, contextPath, currentUser)
    }

    return []
  }, [contextData])

  return (
    <EditorActions
      actions={contextActions}
    />
  )
}

export {
  ContextEditorActions
}
