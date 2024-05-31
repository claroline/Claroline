import React from 'react'
import {useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {LessonEditorSummary} from '#/plugin/lesson/resources/lesson/editor/components/summary'
import {LessonEditorChapter} from '#/plugin/lesson/resources/lesson/editor/components/chapter'

const LessonEditorContent = () => {
  const resourceEditorPath = useSelector(editorSelectors.path) + '/content'

  return (
    <Routes
      path={resourceEditorPath}
      routes={[
        {
          path: '/',
          exact: true,
          component: LessonEditorSummary
        }, {
          path: '/:slug',
          component: LessonEditorChapter
        }
      ]}
    />
  )
}

export {
  LessonEditorContent
}
