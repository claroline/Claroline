import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {selectors} from '#/main/evaluation/sequence/editor/store'
import {EvaluationForm} from '#/main/evaluation/components/form'

const SequenceEditorEvaluation = () => {
  const workspace = useSelector(selectors.workspace)

  return (
    <EditorPage
      title={trans('evaluation', {}, 'evaluation')}
      help={trans('evaluation_help', {}, 'evaluation')}
    >
      <EvaluationForm
        name={selectors.STORE_NAME}
        score={true}
        certification={true}
        workspace={workspace}
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
            label: trans('enable_success_condition_resource_success', {}, 'evaluation'),
            fields: [
              {
                name: 'minSuccess',
                label: trans('resources_count', {}, 'resource'),
                type: 'number',
                required: true,
                options: {
                  min: 0
                }
              }
            ]
          }, {
            name: 'maxFailed',
            label: trans('enable_success_condition_resource_failed', {}, 'evaluation'),
            fields: [
              {
                name: 'maxFailed',
                label: trans('resources_count', {}, 'resource'),
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
    </EditorPage>
  )
}

export {
  SequenceEditorEvaluation
}
