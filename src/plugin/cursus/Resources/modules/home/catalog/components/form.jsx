import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security/permissions'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ContentLoader} from '#/main/app/content/components/loader'

import {HomePage} from '#/plugin/home/tools/home/containers/page'
import {Tab as TabTypes} from '#/plugin/home/prop-types'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogForm = (props) => {
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
      poster={get(props.course, 'poster.url')}
      actions={[
        {
          name: 'export-pdf',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-file-pdf-o',
          label: trans('export-pdf', {}, 'actions'),
          displayed: hasPermission('open', props.course),
          group: trans('transfer'),
          target: ['apiv2_cursus_course_download_pdf', {id: props.course.id}]
        }
      ]}
    >
      <CourseForm
        path={props.path}
        name={selectors.FORM_NAME}
      />
    </HomePage>
  )
}

CatalogForm.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ),

  tabs: T.arrayOf(T.shape(
    TabTypes.propTypes
  )),
  currentTab: T.shape(
    TabTypes.propTypes
  ).isRequired
}

export {
  CatalogForm
}