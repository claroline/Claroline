import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ToolPage} from '#/main/core/tool'
import {MODAL_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'

import {ContentSizing} from '#/main/app/content/components/sizing'
import {CreationType} from '#/plugin/cursus/course/components/type'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
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
          path: props.path
        }],
        group: trans('management'),
        displayed: props.canEdit,
        primary: true
      }
    ]}
  >
    <ContentSizing size="lg" className="mt-4">
      <ContentPlaceholder
        size="lg"
        title={trans('no_course', {}, 'cursus')}
        help={trans('no_course_help', {}, 'cursus')}
      />
    </ContentSizing>

    <ContentSizing size="md" className="mt-4">
      <CreationType {...props} />
    </ContentSizing>

  </ToolPage>

EmptyCourse.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  EmptyCourse
}
