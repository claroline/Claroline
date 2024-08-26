import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {CourseList} from '#/plugin/cursus/course/components/list'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {CreationType} from '#/plugin/cursus/course/components/type'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {MODAL_COURSE_TYPE_CREATION} from '#/plugin/cursus/course/modals/creation'

const CatalogList = (props) =>
  <ToolPage
    breadcrumb={[{
      type: LINK_BUTTON,
      label: trans('catalog', {}, 'cursus'),
      target: props.path
    }]}
    title={trans('catalog', {}, 'cursus')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_course', {}, 'cursus'),
        modal: [MODAL_COURSE_TYPE_CREATION, {
          path: props.path
        }],
        group: trans('management'),
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <CourseList
      path={props.path}
      name={selectors.LIST_NAME}
      url={['apiv2_cursus_course_list']}
    />

    {props.courses.totalResults === 0 &&
      <ContentSizing size="md" className="mt-4">
        <CreationType {...props} />
      </ContentSizing>
    }
  </ToolPage>

CatalogList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  courses: T.object
}

export {
  CatalogList
}
