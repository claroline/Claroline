import React from 'react'
import get from 'lodash/get'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentLoader} from '#/main/app/content/components/loader'

import {route} from '#/plugin/cursus/routing'
import {getInfo} from '#/plugin/cursus/utils'
import {getActions} from '#/plugin/cursus/course/utils'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'

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
          target: props.path + '/catalog',
          displayed: 'desktop' === props.contextType
        }, {
          type: LINK_BUTTON,
          label: get(props.course, 'name', trans('loading')),
          target: !isEmpty(props.course) ? route(props.course, null, props.path) : ''
        }
      ].concat(props.course ? props.breadcrumb : [])}
      primaryAction="edit"
      actions={getActions([props.course], {}, '', props.currentUser)}
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
  contextType: T.string,
  children: T.any
}

Course.defaultProps = {
  breadcrumb: []
}

const CoursePage = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    contextType: toolSelectors.contextType(state)
  })
)(Course)

export {
  CoursePage
}
