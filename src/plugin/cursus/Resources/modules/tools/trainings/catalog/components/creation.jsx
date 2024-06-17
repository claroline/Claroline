import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {CourseForm} from '#/plugin/cursus/course/containers/form'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogCreation = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('catalog', {}, 'cursus'),
        target: props.path + '/catalog'
      }
    ]}
    title={trans('new_course', {}, 'cursus')}
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
      path={props.path+'/catalog'}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

CatalogCreation.propTypes = {
  path: T.string.isRequired
}

export {
  CatalogCreation
}
