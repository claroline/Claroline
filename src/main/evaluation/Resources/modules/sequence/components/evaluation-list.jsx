import React, {useCallback, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {actions as listActions} from '#/main/app/content/list'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as contextSelectors} from '#/main/app/context'

import {EvaluationList} from '#/main/evaluation/components/list'
import {EvaluationSequenceCard} from '#/main/evaluation/sequence/components/card'
import {getEvaluationActions} from '#/main/evaluation/sequence/utils'

const SequenceEvaluationList = (props) => {
  const dispatch = useDispatch()

  const currentUser = useSelector(securitySelectors.currentUser)

  const contextType = useSelector(contextSelectors.type)
  const contextObject = useSelector(contextSelectors.data)
  const contextPath = useSelector(contextSelectors.path)

  const invalidateList = useCallback(() => {
    dispatch(listActions.invalidateData(props.name))
  }, [props.name])

  const evaluationsRefresher = useMemo(() => ({
    add:    invalidateList,
    update: invalidateList,
    delete: invalidateList
  }), [props.name])

  return (
    <EvaluationList
      name={props.name}
      url={props.url}
      contextType={contextType}
      contextId={get(contextObject, 'id')}
      primaryAction={props.primaryAction || 'open'}
      actions={(rows) => getEvaluationActions(rows, evaluationsRefresher, contextPath, currentUser, true)}
      card={EvaluationSequenceCard}
      hasScore={props.hasScore}
      totalScore={props.totalScore}
      customDefinition={[
        {
          name: 'duration',
          type: 'time',
          label: trans('duration'),
          displayed: false,
          filterable: false
        }, {
          name: 'estimatedDuration',
          type: 'time',
          label: trans('estimated_duration'),
          displayed: false,
          filterable: false
        }
      ].concat(props.customDefinition || [])}
    />
  )
}

SequenceEvaluationList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.array, T.string]).isRequired,
  primaryAction: T.string,
  hasScore: T.bool,
  totalScore: T.number,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  }))
}

export {
  SequenceEvaluationList
}
