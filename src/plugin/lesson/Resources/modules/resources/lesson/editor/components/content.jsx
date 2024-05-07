import React, {useEffect} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {Routes} from '#/main/app/router'
import {actions as formActions} from '#/main/app/content/form'
import {selectors as resourceSelectors} from '#/main/core/resource'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonEditorSummary} from '#/plugin/lesson/resources/lesson/editor/components/summary'
import {LessonEditorChapter} from '#/plugin/lesson/resources/lesson/editor/components/chapter'

const LessonEditorContent = () => {
  const resourceEditorPath = useSelector(resourceSelectors.path) + '/edit/content'

  /*const dispatch = useDispatch()
  const lesson = useSelector(selectors.lesson)
  const chapters = useSelector(selectors.treeData)*/

  // load text resource in editor
  /*useEffect(() => {
    dispatch(formActions.load(resourceSelectors.EDITOR_NAME, {resource: lesson, chapters: chapters.children || []}))
  }, [lesson.id])*/

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
