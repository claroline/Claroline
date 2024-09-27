import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {CourseList} from '#/plugin/cursus/course/components/list'
import {CreationType} from '#/plugin/cursus/course/components/type'
import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store'
import {MODAL_COURSE_TYPE_CREATION} from '#/plugin/cursus/course/modals/creation'
import {PageListSection} from '#/main/app/page/components/list-section'

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
          path: props.path + '/course'
        }],
        group: trans('management'),
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <PageListSection>
      <CourseList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={['apiv2_cursus_course_list']}
      >
        <p className="text-center my-5 mt-1">
          <span className="h1 fa fa-graduation-cap mb-3 text-body-tertiary" />
          <b className="h5 d-block">{trans('no_courses', {}, 'cursus')}</b>
          <span className="text-body-secondary">{trans('no_courses_help', {}, 'cursus')}</span>
        </p>
        <CreationType
          path={props.path + '/course'}
          contextType={props.contextType}
          openForm={props.openForm}
        />
      </CourseList>
    </PageListSection>
  </ToolPage>

CatalogList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  contextType: T.string,
  openForm: T.func.isRequired
}

export {
  CatalogList
}
