import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {LogOperationalList} from '#/main/log/components/operational-list'

const CourseEditorHistory = () =>
  <EditorPage
    title={trans('history')}
    help={trans('course_history_help', {}, 'cursus')}
  >
    <LogOperationalList
      autoload=""
      url=""
    />
  </EditorPage>

export {
  CourseEditorHistory
}
