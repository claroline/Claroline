import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseMain} from '#/plugin/cursus/course/containers/main'

const CatalogDetails = (props) =>
  <CoursePage
    basePath={props.path}
    path={props.course ? [
      {
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: props.path
      }, {
        type: LINK_BUTTON,
        label: props.course.name,
        target: route(props.path, props.course)
      }
    ] : undefined}
    currentContext={props.currentContext}
    course={props.course}
    activeSession={props.activeSession}
  >
    {props.course &&
      <CourseMain
        path={props.path}
        course={props.course}
      />
    }
  </CoursePage>

CatalogDetails.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace']),
    data: T.object
  }).isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  activeSession: T.shape({
    id: T.string.isRequired
  })
}

export {
  CatalogDetails
}