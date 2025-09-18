import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {selectors as toolSelectors} from '#/main/core/tool'
import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {makeListReducer} from '#/main/app/content/list'
import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store'
import {WorkspaceEvaluationList} from '#/main/evaluation/workspace/components/evaluation-list'

const STORE_NAME = 'workspaceEvaluations'

const EvaluationDashboardWorkspaces = () => {
  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: { property: 'lastActivityAt', direction: -1 }
  }, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)
  const hasScore = useSelector(selectors.hasScore)
  const totalScore = useSelector(selectors.totalScore)

  return (
    <WorkspaceEvaluationList
      name={STORE_NAME}
      contextType={contextType}
      contextId={contextId}
      url={['apiv2_workspace_evaluation_list', {workspaceId: contextId}]}
      customDefinition={'desktop' === contextType ? [
        {
          name: 'workspace',
          type: 'workspace',
          label: trans('workspace'),
          displayable: true,
          displayed: true,
          filterable: true,
          sortable: true,
          order: 2
        }
      ] : []}
      hasScore={hasScore}
      totalScore={totalScore}
    />
  )
}

export {
  EvaluationDashboardWorkspaces
}
