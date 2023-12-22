import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {BadgeList as BaseBadgeList}  from '#/plugin/open-badge/badge/components/list'
import {selectors} from '#/plugin/open-badge/tools/badges/store'
import {MODAL_TRANSFER} from '#/plugin/open-badge/modals/transfer'

const BadgeList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('all_badges', {}, 'badge'),
      target: ''
    }]}
    subtitle={trans('all_badges', {}, 'badge')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_badge', {}, 'actions'),
        target: `${props.path}/new`,
        displayed: props.canEdit,
        primary: true
      }, {
        name: 'transfer-badges',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-right-left',
        label: trans('transfer_badges', {}, 'actions'),
        modal: [MODAL_TRANSFER],
        displayed: props.canAdministrate,
        primary: false
      }
    ]}
  >
    <ContentSizing size="full">
      <BaseBadgeList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={'workspace' === props.contextType ?
          ['apiv2_badge-class_workspace_badge_list', {workspace: props.contextData.id}] :
          ['apiv2_badge-class_list']
        }
        customDefinition={'workspace' !== props.contextType ? [
          {
            name: 'workspace',
            label: trans('workspace'),
            type: 'workspace',
            displayed: true,
            filterable: true
          }
        ] : []}
      />
    </ContentSizing>
  </ToolPage>

BadgeList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object
}

export {
  BadgeList
}
