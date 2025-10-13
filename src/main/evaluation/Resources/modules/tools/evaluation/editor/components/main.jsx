import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool'

import {selectors} from '#/main/evaluation/tools/evaluation/store'
import {EvaluationEditorOverview} from '#/main/evaluation/tools/evaluation/editor/components/overview'
import {EvaluationEditorSkill} from '#/main/evaluation/tools/evaluation/editor/skill/containers/main'

const EvaluationEditor = () => {
  const evaluationParameters = useSelector(selectors.evaluation)

  console.log(evaluationParameters)

  return (
    <ToolEditor
      additionalData={() => ({
        evaluation: evaluationParameters
      })}
      overviewPage={EvaluationEditorOverview}
      pages={[
        {
          name: 'skills',
          title: trans('skills_frameworks', {}, 'evaluation'),
          component: EvaluationEditorSkill,
          displayed: false
        }
      ]}
    />
  )
}

export {
  EvaluationEditor
}
