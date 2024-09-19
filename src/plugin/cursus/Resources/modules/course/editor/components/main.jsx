import React, {useEffect} from 'react'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {route} from '#/plugin/cursus/routing'
import {hasPermission} from '#/main/app/security'
import {Editor} from '#/main/app/editor/components/main'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

import {CourseEditorHistory} from '#/plugin/cursus/course/editor/components/history'
import {CourseEditorActions} from '#/plugin/cursus/course/editor/components/actions'
import {CourseEditorOverview} from '#/plugin/cursus/course/editor/components/overview'
import {CourseEditorAppearance} from '#/plugin/cursus/course/editor/components/appearance'
import {CourseEditorWorkspaces} from '#/plugin/cursus/course/editor/components/workspaces'
import {CourseEditorCanceledSessions} from '#/plugin/cursus/course/editor/components/list'
import {CourseEditorPermissions} from '#/plugin/cursus/course/editor/components/permissions'
import {CourseEditorRegistration} from '#/plugin/cursus/course/editor/components/registration'

import {selectors} from '#/plugin/cursus/course/store'

const CourseEditor = (props) => {

  useEffect(() => {
    props.openForm(props.slug)
  }, [props.slug])

  return (
    <Editor
      path={route(props.course, null, props.path) + '/edit'}
      title={get(props.course, 'name')}
      name={selectors.FORM_NAME}
      target={['apiv2_cursus_course_update', {id: props.course.id}]}
      canAdministrate={hasPermission('administrate', props.course)}
      close={props.path}
      defaultPage="overview"
      historyPage={CourseEditorHistory}
      actionsPage={CourseEditorActions}
      overviewPage={CourseEditorOverview}
      appearancePage={CourseEditorAppearance}
      permissionsPage={CourseEditorPermissions}
      pages={[
        {
          name: 'workspaces',
          title: trans('workspaces'),
          render: () => (
            <CourseEditorWorkspaces
              name={selectors.FORM_NAME}
              course={props.course}
              contextType={props.contextType}
              update={props.update}
            />
          )
        }, {
          name: 'registration',
          title: trans('registration'),
          render: () => (
            <CourseEditorRegistration
              name={selectors.FORM_NAME}
              course={props.course}
              update={props.update}
            />
          )
        }, {
          name: 'canceled',
          title: trans('canceled_sessions', {}, 'cursus'),
          help: trans('canceled_sessions_help', {}, 'cursus'),
          render: () => (
            <CourseEditorCanceledSessions
              path={props.path}
              course={props.course}
            />
          )
        }
      ].concat(props.pages || [])}
    />
  )
}

CourseEditor.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  update: T.func.isRequired,
  contextType: T.string,
  pages: T.array,
  openForm: T.func.isRequired,
  slug: T.string
}

export {
  CourseEditor
}
