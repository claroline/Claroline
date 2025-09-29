import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {useReducer} from '#/main/app/store/hooks/useReducer'
import {makeListReducer} from '#/main/app/content/list'
import {makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {API_FETCH_PENDING} from '#/main/app/api/fetch/store/actions'

import {SEQUENCE_RELOAD} from '#/main/evaluation/sequence/store/actions'
import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'
import {ResourceEvaluationList} from '#/main/evaluation/resource/components/evaluation-list'

const STORE_NAME = 'sequenceResourceEvaluations'

const SequenceDashboardResources = () => {
  const reducer = useMemo(() => makeListReducer(STORE_NAME, {
    sortBy: { property: 'lastActivityAt', direction: -1 }
  }, {
    loaded: makeReducer(false, {
      [makeInstanceAction(API_FETCH_PENDING, 'evaluationSequence')]: () => false
    }),
    invalidated: makeReducer(false, {
      [SEQUENCE_RELOAD]: () => true
    })
  }), [STORE_NAME])
  useReducer(STORE_NAME, reducer)

  const sequenceId = useSelector(sequenceSelectors.id)

  return (
    <ResourceEvaluationList
      name={STORE_NAME}
      url={['apiv2_resource_evaluation_list', {parentType: 'sequence', parentId: sequenceId}]}
      primaryAction="open"
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
  SequenceDashboardResources
}
