import React from 'react'
import {useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {selectors as editorSelectors} from '#/main/core/tool/editor'

import {EditorSkillList} from '#/main/evaluation/tools/evaluation/editor/skill/components/list'
import {EditorSkillForm} from '#/main/evaluation/tools/evaluation/editor/skill/components/form'

const EvaluationEditorSkill = () => {
  const editorPath = useSelector(editorSelectors.path)

  return (
    <Routes
      path={editorPath+'/skills'}
      routes={[
        {
          path: '/',
          exact: true,
          component: EditorSkillList
        }, {
          path: '/:id',
          component: EditorSkillForm
        }
      ]}
    />
  )
}

export {
  EvaluationEditorSkill
}
