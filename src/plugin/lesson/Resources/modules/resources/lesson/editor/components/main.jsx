import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {ResourceEditor} from '#/main/core/resource'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonEditorContent} from '#/plugin/lesson/resources/lesson/editor/components/content'
import {LessonEditorAppearance} from '#/plugin/lesson/resources/lesson/editor/components/appearance'

const LessonEditor = () => {
  const lesson = useSelector(selectors.lesson)
  const chapters = useSelector(selectors.treeData)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: lesson,
        chapters: chapters.children || []
      })}
      defaultPage="content"
      appearancePage={LessonEditorAppearance}
      pages={[
        {
          name: 'content',
          title: trans('content'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          component: LessonEditorContent
        }
      ]}
    />
  )
}

export {
  LessonEditor
}
