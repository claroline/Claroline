import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/cursus/routing'
import {getInfo} from '#/plugin/cursus/utils'
import {getActions} from '#/plugin/cursus/course/utils'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'

const CoursePage = (props) => {
  if (isEmpty(props.course)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('training_loading', {}, 'cursus')}
      />
    )
  }

  const baseActions = [
    {
      name: 'edit',
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-pencil',
      label: trans('edit', {}, 'actions'),
      target: route(props.basePath, props.course) + '/edit',
      displayed: hasPermission('edit', props.course),
      primary: true
    }, {
      name: 'export-pdf',
      type: URL_BUTTON,
      icon: 'fa fa-fw fa-file-pdf-o',
      label: trans('export-pdf', {}, 'actions'),
      displayed: hasPermission('open', props.course),
      group: trans('transfer'),
      target: ['apiv2_cursus_course_download_pdf', {id: props.course.id}]
    }
  ]

  return (
    <ToolPage
      path={props.path}
      title={props.course.name}
      subtitle={get(props.activeSession, 'name')}
      poster={getInfo(props.course, props.activeSession, 'poster.url')}
      meta={{
        title: `${trans('trainings', {}, 'tools')} - ${props.course.name}`,
        description: props.course.description
      }}

      primaryAction="edit"
      actions={getActions([props.course], props.currentContext, {}, props.basePath).then(pluginActions => {
        if (props.actions instanceof Promise) {
          return props.actions.then(promisedActions => promisedActions.concat(pluginActions, baseActions))
        }

        return (props.actions || []).concat(pluginActions, baseActions)
      })}
    >
      {props.children}
    </ToolPage>
  )
}

CoursePage.propTypes = {
  path: T.array,
  basePath: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  primaryAction: T.string,
  actions: T.array,
  course: T.shape(
    CourseTypes.propTypes
  ),
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  children: T.any
}

CoursePage.defaultProps = {
  path: []
}

export {
  CoursePage
}
