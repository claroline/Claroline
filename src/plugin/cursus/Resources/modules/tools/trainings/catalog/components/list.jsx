import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {CourseList} from '#/plugin/cursus/course/components/list'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'

const CatalogList = (props) =>
  <ToolPage
    breadcrumb={[{
      type: LINK_BUTTON,
      label: trans('catalog', {}, 'cursus'),
      target: props.path + '/catalog'
    }]}
    title={trans('catalog', {}, 'cursus')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_course', {}, 'cursus'),
        target: `${props.path}/catalog/new`,
        group: trans('management'),
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <CourseList
      path={props.path+'/catalog'}
      name={selectors.LIST_NAME}
      url={['apiv2_cursus_course_list']}
    />
  </ToolPage>

CatalogList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  CatalogList
}
