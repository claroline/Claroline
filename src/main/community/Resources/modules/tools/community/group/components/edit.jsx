import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {GroupPage} from '#/main/community/group/components/page'
import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {GroupForm} from '#/main/community/group/components/form'

import {selectors} from '#/main/community/tools/community/group/store/selectors'

const GroupEdit = (props) =>
  <GroupPage
    path={props.path}
    breadcrumb={[
      {
        type: LINK_BUTTON,
        label: trans('edition'),
        target: '' // current page, link is not needed
      }
    ]}
    group={props.group}
    reload={props.reload}
  >
    <GroupForm
      path={`${props.path}/groups/${props.group ? props.group.id : ''}`}
      name={selectors.FORM_NAME}
    />
  </GroupPage>

GroupEdit.propTypes = {
  path: T.string.isRequired,
  group: T.shape(
    GroupTypes.propTypes
  ),
  reload: T.func.isRequired
}

export {
  GroupEdit
}
