import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {CourseList} from '#/plugin/cursus/course/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/editor/store'

const TrainingsEditorArchive = (props) =>
  <EditorPage
    title={trans('archived_trainings', {}, 'cursus')}
    help={trans('archived_trainings_help', {}, 'cursus')}
  >
    <CourseList
      path={props.path}
      name={selectors.STORE_NAME}
      url={['apiv2_cursus_course_list_archived']}
    />
  </EditorPage>

export {
  TrainingsEditorArchive
}
