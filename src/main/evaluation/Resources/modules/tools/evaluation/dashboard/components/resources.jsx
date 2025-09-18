import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {useReducer} from '#/main/app/store/hooks/useReducer'
import {makeListReducer} from '#/main/app/content/list'
import {makeReducer} from '#/main/app/store/reducer'
import {trans} from '#/main/app/intl'
import {selectors as contextSelectors} from '#/main/app/context'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store'

import {ResourceEvaluationList} from '#/main/evaluation/resource/components/evaluation-list'
import get from 'lodash/get'

const STORE_NAME = 'resourceEvaluations'

const EvaluationDashboardResources = () => {
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

  const contextType = useSelector(contextSelectors.type)
  const contextObject = useSelector(contextSelectors.data)

  return (
    <ResourceEvaluationList
      name={STORE_NAME}
      url={['apiv2_resource_evaluation_list', 'workspace' === contextType ?
        {parentType: 'workspace', parentId: get(contextObject, 'id')} : undefined
      ]}
      hasScore={true}
      customDefinition={[
        {
          name: 'resourceNode',
          type: 'resource',
          label: trans('resource'),
          displayable: true,
          displayed: true,
          filterable: true,
          sortable: true,
          order: 2
        }
      ]}
    />
  )
}

export {
  EvaluationDashboardResources
}
