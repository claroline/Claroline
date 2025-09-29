import React, {useMemo} from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {makeReducer, useReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list'
import {selectors as toolSelectors} from '#/main/core/tool'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store'

import {SequenceEvaluationList} from '#/main/evaluation/sequence/components/evaluation-list'
import {selectors as contextSelectors} from '#/main/app/context'
import get from 'lodash/get'

const STORE_NAME = 'sequenceEvaluations'

const EvaluationDashboardSequences = () => {
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
  const contextObject = useSelector(contextSelectors.data)

  return (
    <SequenceEvaluationList
      name={STORE_NAME}
      url={['apiv2_sequence_evaluation_list', 'workspace' === contextType ?
        {parentType: 'workspace', parentId: get(contextObject, 'id')} : undefined
      ]}
      hasScore={true}
      customDefinition={[
        {
          name: 'sequence',
          type: 'sequence',
          label: trans('sequence', {}, 'evaluation'),
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
  EvaluationDashboardSequences
}
