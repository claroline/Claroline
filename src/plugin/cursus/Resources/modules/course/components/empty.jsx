import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

import {ContentSizing} from '#/main/app/content/components/sizing'
import {CreationType} from '#/plugin/cursus/course/components/type'
import {MODAL_COURSE_TYPE_CREATION} from '#/plugin/cursus/course/modals/creation'

const EmptyCourse = (props) =>
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_course', {}, 'cursus'),
        modal: [MODAL_COURSE_TYPE_CREATION, {
          path: props.path,
          contextId: props.contextId
        }],
        group: trans('management'),
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <ContentSizing size="md">
      <p className="text-center my-5">
        <span className="h1 fa fa-graduation-cap mb-3 text-body-tertiary"/>
        <b className="h5 d-block">{trans('no_course', {}, 'cursus')}</b>
        <span className="text-body-secondary">{trans('no_course_help', {}, 'cursus')}</span>
      </p>
      <CreationType {...props} />
    </ContentSizing>

  </ToolPage>

EmptyCourse.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  contextId: T.string.isRequired
}

export {
  EmptyCourse
}
