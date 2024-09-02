import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const CourseEditorHistory = () =>
  <EditorPage
    title={trans('history')}
    help={trans('course_history_help', {}, 'cursus')}
  />

export {
  CourseEditorHistory
}
