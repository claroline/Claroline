import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'

import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {route} from '#/plugin/cursus/routing'
import {getInfo} from '#/plugin/cursus/utils'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {CourseMain} from '#/plugin/cursus/course/containers/main'

const CatalogDetails = (props) => {
  if (isEmpty(props.course)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('training_loading', {}, 'cursus')}
      />
    )
  }

  return (
    <HomePage
      breadcrumb={[
        {
          type: LINK_BUTTON,
          label: props.course.name,
          target: route(props.path, props.course)
        }
      ]}
      tabs={props.tabs}
      currentTab={props.currentTab}
      title={props.course.name}
      subtitle={get(props.activeSession, 'name')}
      poster={getInfo(props.course, props.activeSession, 'poster')}
      actions={[
        {
          name: 'export-pdf',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-file-pdf',
          label: trans('export-pdf', {}, 'actions'),
          displayed: hasPermission('open', props.course),
          group: trans('transfer'),
          target: ['apiv2_cursus_course_download_pdf', {id: props.course.id}]
        }
      ]}
    >
      <CourseMain
        path={props.path}
        course={props.course}
      />
    </HomePage>
  )
}

CatalogDetails.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),
  activeSession: T.shape(
    SessionTypes.propTypes
  ),

  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(
    TabTypes.propTypes
  ).isRequired
}

export {
  CatalogDetails
}
