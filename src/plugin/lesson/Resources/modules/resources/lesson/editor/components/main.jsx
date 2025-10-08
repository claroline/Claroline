import React from 'react'
import {useSelector} from 'react-redux'

import {ResourceEditor} from '#/main/core/resource'

import {selectors} from '#/plugin/lesson/resources/lesson/store'
import {LessonEditorAppearance} from '#/plugin/lesson/resources/lesson/editor/components/appearance'

const LessonEditor = () => {
  const lesson = useSelector(selectors.lesson)

  return (
    <ResourceEditor
      additionalData={() => ({
        resource: lesson
      })}
      appearancePage={LessonEditorAppearance}
    />
  )
}

export {
  LessonEditor
}
