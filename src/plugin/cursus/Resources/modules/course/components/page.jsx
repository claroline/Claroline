import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/cursus/routing'
import {getInfo} from '#/plugin/cursus/utils'
import {getActions} from '#/plugin/cursus/course/utils'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'

import {selectors as securitySelectors} from '#/main/app/security/store'

const Course = (props) => {
  if (isEmpty(props.course)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('training_loading', {}, 'cursus')}
      />
    )
  }

  return (
    <ToolPage
      className="training-page"
      title={props.course.name}
      subtitle={get(props.activeSession, 'name')}
      poster={getInfo(props.course, props.activeSession, 'poster')}
      meta={{
        title: `${trans('trainings', {}, 'tools')} - ${props.course.name}`,
        description: props.course.description
      }}
      path={[
        {
          type: LINK_BUTTON,
          label: trans('catalog', {}, 'cursus'),
          target: props.path + '/catalog'
        }, {
          type: LINK_BUTTON,
          label: get(props.course, 'name', trans('loading')),
          target: !isEmpty(props.course) ? route(props.course, null, props.path) : ''
        }
      ].concat(props.course ? props.breadcrumb : [])}
      primaryAction="edit"
      actions={getActions([props.course], {}, props.path, props.currentUser)}
    >
      {props.children}
    </ToolPage>
  )
}

Course.propTypes = {
  path: T.string,
  breadcrumb: T.array,
  course: T.shape(
    CourseTypes.propTypes
  ),
  activeSession: T.shape(
    SessionTypes.propTypes
  ),
  currentUser: T.object,
  children: T.any
}

Course.defaultProps = {
  breadcrumb: []
}

const CoursePage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state)
  })
)(Course)

export {
  CoursePage
}
