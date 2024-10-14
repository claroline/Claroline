import React, {useEffect} from 'react'
import { useHistory } from 'react-router-dom'
import get from 'lodash/get'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {route} from '#/plugin/cursus/routing'
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
  const history = useHistory()

  useEffect(() => {
    if (props.isNew) {
      props.openForm(null, CourseTypes.defaultProps, props.course.workspace)
    } else {
      props.openForm(props.slug)
    }
  }, [props.isNew, props.slug])

  return (
    <Editor
      path={props.isNew ? (props.contextType === 'workspace' ? props.path + '/new' : props.path + '/course/new') : route(props.course, null, props.path) + '/edit'}
      title={get(props.course, 'name', trans('new_course', {}, 'cursus'))}
      name={selectors.FORM_NAME}
      target={(course, isNew) => isNew ? ['apiv2_cursus_course_create'] : ['apiv2_cursus_course_update', {id: props.course.id}]}
      canAdministrate={props.canAdministrate}
      onSave={(course, isNew) => {
        const newSlug = course.slug

        if (!isNew && props.course.slug !== newSlug) {
          const newUrl = `${props.path}/course/${newSlug}/edit`
          history.push(newUrl)
        }
      }}
      close={props.path}
      defaultPage="overview"
      historyPage={!props.isNew ? CourseEditorHistory : undefined}
      actionsPage={!props.isNew ? CourseEditorActions : undefined}
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
          disabled: props.isNew === true,
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
  isNew: T.bool,
  pages: T.array,
  slug: T.string,
  contextType: T.string,
  update: T.func.isRequired,
  canAdministrate: T.bool,
  openForm: T.func.isRequired
}

export {
  CourseEditor
}
