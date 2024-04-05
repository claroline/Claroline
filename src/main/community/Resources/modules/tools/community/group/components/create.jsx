import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolPage} from '#/main/core/tool'
import {LINK_BUTTON} from '#/main/app/buttons'

import {GroupForm} from '#/main/community/group/components/form'
import {selectors} from '#/main/community/tools/community/group/store'

const GroupCreate = (props) =>
  <ToolPage
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('groups', {}, 'community'),
        target: `${props.path}/groups`
      }
    ]}
    title={trans('new_group', {}, 'community')}
  >
    <GroupForm
      className="mt-3"
      path={props.path}
      name={selectors.FORM_NAME}
    />
  </ToolPage>

GroupCreate.propTypes = {
  path: T.string
}

export {
  GroupCreate
}
