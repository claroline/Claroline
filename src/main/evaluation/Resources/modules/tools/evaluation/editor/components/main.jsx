import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {ToolEditor, selectors as toolSelectors} from '#/main/core/tool'

import {EvaluationEditorOverview} from '#/main/evaluation/tools/evaluation/editor/components/overview'
import {EvaluationEditorActions} from '#/main/evaluation/tools/evaluation/editor/components/actions'
import {EvaluationToolAppearance} from '#/main/evaluation/tools/evaluation/editor/components/appearance'

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
    />
  )
}

export {
  EvaluationEditor
}
