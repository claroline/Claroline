import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'

const CoursePage = (props) => {
  if (isEmpty(props.course)) {
    return (
      <ContentLoader
        size="lg"
        description="Nous chargeons la formation..."
      />
    )
  }

  return (
    <ToolPage
      path={props.path}
      title={props.course.name}
      subtitle={props.course.code}
      poster={props.course.poster ? props.course.poster.url : undefined}
      primaryAction={props.primaryAction}
      actions={props.actions}
    >
      {props.children}
    </ToolPage>
  )
}

CoursePage.propTypes = {
  path: T.array,
  primaryAction: T.string,
  actions: T.array,
  course: T.shape(
    CourseTypes.propTypes
  ),
  children: T.any
}

CoursePage.defaultProps = {
  path: []
}

export {
  CoursePage
}
