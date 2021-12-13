import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const WorkspacesMenu = (props) =>
  <MenuSection
    {...omit(props, 'path', 'creatable')}
    title={trans('workspaces', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'registered',
          type: LINK_BUTTON,
          label: trans('my_workspaces', {}, 'workspace'),
          target: props.path+'/registered',
          displayed: props.authenticated
        }, {
          name: 'public',
          type: LINK_BUTTON,
          label: trans('public_workspaces', {}, 'workspace'),
          target: props.path+'/public'
        }, {
          name: 'managed',
          type: LINK_BUTTON,
          label: trans('managed_workspaces', {}, 'workspace'),
          target: props.path+'/managed',
          displayed: props.authenticated
        }, {
          name: 'model',
          type: LINK_BUTTON,
          label: trans('workspace_models', {}, 'workspace'),
          target: props.path+'/model',
          displayed: props.canCreate
        }, {
          name: 'archive',
          type: LINK_BUTTON,
          label: trans('workspace_archived', {}, 'workspace'),
          target: props.path+'/archived',
          displayed: props.canArchive
        }, {
          name: 'new',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('create_workspace', {}, 'workspace'),
          target: props.path+'/new',
          displayed: props.canCreate
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

WorkspacesMenu.propTypes = {
  path: T.string,
  authenticated: T.bool.isRequired,
  canCreate: T.bool.isRequired,
  canArchive: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  WorkspacesMenu
}
