import React, {useCallback, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import get from 'lodash/get'

import {selectors as securitySelectors} from '#/main/app/security'
import {actions as listActions} from '#/main/app/content/list'
import {selectors as contextSelectors} from '#/main/app/context'

import {EvaluationList} from '#/main/evaluation/components/list'
import {EvaluationWorkspaceCard} from '#/main/evaluation/workspace/components/card'
import {getActions} from '#/main/evaluation/workspace/utils'

const WorkspaceEvaluationList = (props) => {
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
      actions={(rows) => getActions(rows, evaluationsRefresher, contextPath, currentUser, true)}
      card={EvaluationWorkspaceCard}
      hasScore={props.hasScore}
      totalScore={props.totalScore}
      customDefinition={props.customDefinition}
    />
  )
}

WorkspaceEvaluationList.propTypes = {
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
  WorkspaceEvaluationList
}
