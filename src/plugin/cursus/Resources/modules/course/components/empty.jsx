import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Button} from '#/main/app/action/components/button'
import {ContentSizing} from '#/main/app/content/components/sizing'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const EmptyCourse = (props) =>
  <ToolPage
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_course', {}, 'cursus'),
        target: `${props.path}/new`,
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
      <Button
        className="btn btn-primary w-100 my-3"
        size="lg"
        type={LINK_BUTTON}
        target={`${props.path}/new`}
        label={trans('add_course', {}, 'cursus')}
      />
    </ContentSizing>
  </ToolPage>

EmptyCourse.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  EmptyCourse
}
