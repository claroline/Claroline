import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {BadgeList as BaseBadgeList}  from '#/plugin/open-badge/badge/components/list'
import {selectors} from '#/plugin/open-badge/tools/badges/store'

const BadgeList = props =>
  <ToolPage
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
      }
    ]}
  >
    {/*<PageSection size="full">*/}
      <BaseBadgeList
        /*flush={true}*/
        path={props.path}
        name={selectors.LIST_NAME}
        url={'workspace' === props.contextType ?
          ['apiv2_badge-class_workspace_badge_list', {workspace: props.contextId}] :
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
    {/*</PageSection>*/}
  </ToolPage>

BadgeList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string
}

export {
  BadgeList
}
