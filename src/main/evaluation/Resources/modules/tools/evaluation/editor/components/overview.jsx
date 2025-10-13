import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {selectors as toolSelectors} from '#/main/core/tool'
import {ToolEditorOverview} from '#/main/core/tool/editor'

import {EvaluationForm} from '#/main/evaluation/components/form'

const EvaluationEditorOverview = () => {
  const contextType = useSelector(toolSelectors.contextType)
  const contextData = useSelector(toolSelectors.contextData)

  return (
    <ToolEditorOverview>
      {'workspace' === contextType &&
        <EvaluationForm
          name={toolSelectors.EDITOR_NAME}
          score={true}
          certification={true}
          workspace={contextData}
          successConditions={[
            {
              name: 'score',
              label: trans('enable_success_condition_score', {}, 'evaluation'),
              help: trans('enable_success_condition_score_help', {}, 'evaluation'),
              displayed: (evaluationData) => get(evaluationData, 'scored', false),
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
            }, {
              name: 'minSuccess',
              label: trans('enable_success_condition_sequence_success', {}, 'evaluation'),
              fields: [
                {
                  name: 'minSuccess',
                  label: trans('sequences_count', {}, 'evaluation'),
                  type: 'number',
                  required: true,
                  options: {
                    min: 0
                  }
                }
              ]
            }, {
              name: 'maxFailed',
              label: trans('enable_success_condition_sequence_failed', {}, 'evaluation'),
              fields: [
                {
                  name: 'maxFailed',
                  label: trans('sequences_count', {}, 'evaluation'),
                  type: 'number',
                  required: true,
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]}
        />
      }
    </ToolEditorOverview>
  )
}

export {
  EvaluationEditorOverview
}
