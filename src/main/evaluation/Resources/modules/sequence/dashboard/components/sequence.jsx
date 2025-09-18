import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list'

import {API_FETCH_PENDING} from '#/main/app/api/fetch/store/actions'
import {SEQUENCE_RELOAD} from '#/main/evaluation/sequence/store/actions'
import {selectors as sequenceSelectors} from '#/main/evaluation/sequence/store'
import {SequenceEvaluationList} from '#/main/evaluation/sequence/components/evaluation-list'

const STORE_NAME = 'sequenceEvaluations'

const SequenceDashboardSequence = () => {
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
  const hasScore = useSelector(sequenceSelectors.hasScore)
  const totalScore = useSelector(sequenceSelectors.totalScore)

  return (
    <SequenceEvaluationList
      name={STORE_NAME}
      url={['apiv2_sequence_evaluation_list', {parentType: 'sequence', parentId: sequenceId}]}
      hasScore={hasScore}
      totalScore={totalScore}
    />
  )
}

export {
  SequenceDashboardSequence
}
