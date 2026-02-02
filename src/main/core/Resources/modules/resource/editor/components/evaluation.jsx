import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'
import {actions as formActions} from '#/main/app/content/form'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {EvaluationForm} from '#/main/evaluation/components/form'
import {selectors} from '#/main/core/resource/editor/store'
import {supportAttempts, supportScore} from '#/main/core/resource/utils'

const ResourceEditorEvaluation = ({
  successConditions = []
}) => {
  const dispatch = useDispatch()

  const workspace = useSelector(selectors.workspace)
  const resourceNode = useSelector(selectors.resourceNode)
  const evaluationParameters = useSelector(resourceSelectors.evaluationParameters)
  const score = supportScore(resourceNode)
  const attempts = supportAttempts(resourceNode)

  useEffect(() => {
    dispatch(formActions.load(selectors.STORE_NAME, {evaluation: evaluationParameters}))
  }, [get(resourceNode, 'id')])

  return (
    <EditorPage
      title={trans('parameters')}
      help={trans('evaluation_help', {}, 'evaluation')}
    >
      <EvaluationForm
        name={selectors.STORE_NAME}
        workspace={workspace}
        score={score}
        certification={false}
        attempts={attempts}
        successConditions={[
          {
            name: 'score',
            label: trans('enable_success_condition_score', {}, 'evaluation'),
            help: trans('enable_success_condition_score_help', {}, 'evaluation'),
            displayed: (evaluationData) => score && evaluationData && !!evaluationData.scored,
            fields: [
              {
                name: 'score',
                label: trans('success_score', {}, 'evaluation'),
                type: 'number',
                required: true,
                options: {
                  min: 0,
                  max: 100,
                  unit: '%'
                }
              }
            ]
          }
        ].concat(successConditions)}
      />
    </EditorPage>
  )
}

ResourceEditorEvaluation.propTypes = {
  /**
   * The list of implemented success conditions.
   */
  successConditions: T.arrayOf(T.shape({
    /**
     * The name of the condition.
     */
    name: T.string.isRequired,
    /**
     * The label for the toggle button.
     */
    label: T.string.isRequired,
    /**
     * Additional help messages to describe the condition.
     */
    help: T.oneOfType([T.string, T.arrayOf(T.string)]),
    /**
     * The list of form fields to configure the success condition
     */
    fields: T.arrayOf(T.object)
  }))
}

export {
  ResourceEditorEvaluation
}
