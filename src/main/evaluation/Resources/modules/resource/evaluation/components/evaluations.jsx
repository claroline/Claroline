import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {useReducer} from '#/main/app/store/hooks/useReducer'
import {makeListReducer} from '#/main/app/content/list'
import {makeReducer} from '#/main/app/store/reducer'

import {RESOURCE_LOAD, selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceEvaluationList} from '#/main/evaluation/resource/components/evaluation-list'

const STORE_NAME = 'resourceEvaluations'

const ResourceDashboardEvaluations = () => {
  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: { property: 'lastActivityAt', direction: -1 }
  }, {
    invalidated: makeReducer(false, {
      [RESOURCE_LOAD]: () => true
    })
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  const resourceId = useSelector(resourceSelectors.id)
  const hasScore = useSelector(resourceSelectors.hasScore)
  const totalScore = useSelector(resourceSelectors.totalScore)

  return (
    <ResourceEvaluationList
      name={STORE_NAME}
      url={['apiv2_resource_evaluation_list', {parentType: 'resource', parentId: resourceId}]}
      hasScore={hasScore}
      totalScore={totalScore}
    />
  )
}

export {
  ResourceDashboardEvaluations
}
