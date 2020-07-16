import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
import {CoursePage} from '#/plugin/cursus/course/components/page'
import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/tools/cursus/catalog/store'

const CatalogForm = (props) => {
  if (!props.isNew) {
    return (
      <CoursePage
        path={props.course ? [
          {
            type: LINK_BUTTON,
            label: trans('catalog', {}, 'cursus'),
            target: `${props.path}/catalog`
          }, {
            type: LINK_BUTTON,
            label: props.course.name,
            target: route(props.course)
          }
        ] : undefined}
        primaryAction="edit"
        actions={props.course ? [
          {
            name: 'edit',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            target: route(props.course) + '/edit',
            group: trans('management'),
            primary: true
          }
        ] : undefined}
        course={props.course}
      >
        <CourseForm
          name={selectors.FORM_NAME}
          course={props.formData}
          cancel={{
            type: LINK_BUTTON,
            target: route(props.course),
            exact: true
          }}
        />
      </CoursePage>
    )
  }

  return (
    <ToolPage
      title={trans('cursus', {}, 'tools')}
      subtitle={trans('new_course', {}, 'cursus')}
      primaryAction="add"
      actions={[
        {
          name: 'add',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_course', {}, 'cursus'),
          target: `${props.path}/catalog/new`,
          group: trans('management'),
          primary: true
        }
      ]}
    >
      <CourseForm
        name={selectors.FORM_NAME}
        cancel={{
          type: LINK_BUTTON,
          target: `${props.path}/catalog`,
          exact: true
        }}
      />
    </ToolPage>
  )
}

CatalogForm.propTypes = {
  path: T.string.isRequired,
  isNew: T.bool.isRequired,
  formData: T.shape(
    CourseTypes.propTypes
  ),
  course: T.shape(
    CourseTypes.propTypes
  )
}

export {
  CatalogForm
}