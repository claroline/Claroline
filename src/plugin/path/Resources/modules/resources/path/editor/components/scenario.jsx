import React from 'react'
import {useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {PathEditorSummary} from '#/plugin/path/resources/path/editor/components/summary'
import {PathEditorStep} from '#/plugin/path/resources/path/editor/components/step'

const EditorScenario = () => {
  const resourceEditorPath = useSelector(editorSelectors.path) + '/steps'

  return (
    <Routes
      path={resourceEditorPath}
      routes={[
        {
          path: '/',
          exact: true,
          component: PathEditorSummary
        }, {
          path: '/:slug',
          component: PathEditorStep
        }
      ]}
    />
  )
}

export {
  EditorScenario
}
