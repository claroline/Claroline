import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ToolEditor, selectors as toolSelectors} from '#/main/core/tool'

import {EvaluationEditorOverview} from '#/main/evaluation/tools/evaluation/editor/components/overview'
import {EvaluationEditorActions} from '#/main/evaluation/tools/evaluation/editor/components/actions'
import {EvaluationToolAppearance} from '#/main/evaluation/tools/evaluation/editor/components/appearance'
import {EvaluationEditorSkill} from '#/main/evaluation/tools/evaluation/editor/skill/containers/main'

const EvaluationEditor = () => {
  const contextData = useSelector(toolSelectors.contextData)

  return (
    <ToolEditor
      additionalData={() => ({
        evaluation: get(contextData, 'evaluation')
      })}
      overviewPage={EvaluationEditorOverview}
      actionsPage={EvaluationEditorActions}
      appearancePage={EvaluationToolAppearance}
      pages={[
        {
          name: 'skills',
          title: trans('skills_frameworks', {}, 'evaluation'),
          component: EvaluationEditorSkill
        }
      ]}
    />
  )
}

export {
  EvaluationEditor
}
