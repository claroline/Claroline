import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogCreation = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: props.path
      }, {
        label: trans('new_course', {}, 'cursus')
      }
    ]}
    title={trans('trainings', {}, 'tools')}
    subtitle={trans('new_course', {}, 'cursus')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_course', {}, 'cursus'),
        target: `${props.path}/new`,
        group: trans('management'),
        primary: true
      }
    ]}
  >
    <CourseForm
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

CatalogCreation.propTypes = {
  path: T.string.isRequired
}

export {
  CatalogCreation
}
