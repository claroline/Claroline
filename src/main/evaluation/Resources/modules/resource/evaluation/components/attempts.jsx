import React, {useCallback, useMemo} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {actions as listActions, makeListReducer} from '#/main/app/content/list'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool'

import {RESOURCE_LOAD, selectors as resourceSelectors} from '#/main/core/resource/store'
import {ResourceCard} from '#/main/evaluation/resource/components/card'

import {EvaluationList} from '#/main/evaluation/components/list'
import {getAttemptActions} from '#/main/evaluation/resource/utils'
import {trans} from '#/main/app/intl'

const STORE_NAME = 'resourceAttempts'

const ResourceDashboardAttempts = () => {
  const dispatch = useDispatch()

  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: { property: 'lastActivityAt', direction: -1 }
  }, {
    invalidated: makeReducer(false, {
      [RESOURCE_LOAD]: () => true
    })
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  const currentUser = useSelector(securitySelectors.currentUser)
  const contextType = useSelector(toolSelectors.contextType)
  const contextId = useSelector(toolSelectors.contextId)

  const resourcePath = useSelector(resourceSelectors.path)
  const resourceId = useSelector(resourceSelectors.id)
  const hasScore = useSelector(resourceSelectors.hasScore)
  const totalScore = useSelector(resourceSelectors.totalScore)

  const invalidateList = useCallback(() => {
    dispatch(listActions.invalidateData(STORE_NAME))
  }, [STORE_NAME])

  const evaluationsRefresher = {
    add:    invalidateList,
    update: invalidateList,
    delete: invalidateList
  }

  return (
    <EvaluationList
      name={STORE_NAME}
      contextType={contextType}
      contextId={contextId}
      url={['apiv2_resource_attempt_list', {resourceId: resourceId}]}
      primaryAction="open"
      actions={(rows) => getAttemptActions(rows, evaluationsRefresher, resourcePath, currentUser, true)}
      card={ResourceCard}
      hasScore={hasScore}
      totalScore={totalScore}
      customDefinition={[
        {
          name: 'duration',
          type: 'time',
          label: trans('duration'),
          displayed: false,
          filterable: false
        }
      ]}
    />
  )
}

export {
  ResourceDashboardAttempts
}
