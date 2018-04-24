import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {matchPath, withRouter} from '#/main/core/router'
import {trans} from '#/main/core/translation'
import {number} from '#/main/core/intl'
import {t_res} from '#/main/core/resource/translation'
import {currentUser} from '#/main/core/user/current'

import {getSimpleAccessRule, hasCustomRules} from '#/main/core/resource/rights'

/*import {MODAL_DELETE_CONFIRM}      from '#/main/core/layout/modal'*/
import {MODAL_RESOURCE_PROPERTIES} from '#/main/core/resource/components/modal/edit-properties'
import {MODAL_RESOURCE_RIGHTS}     from '#/main/core/resource/components/modal/edit-rights'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {
  PageActions,
  PageGroupActions,
  PageAction,
  FullScreenAction,
  MoreAction
} from '#/main/core/layout/page/components/page-actions'

const PublishAction = props =>
  <PageAction
    type="callback"
    label={t_res(props.resourceNode.meta.published ? 'resource_unpublish' : 'resource_publish')}
    icon={classes(props.resourceNode.meta.published ? 'fa-eye' : 'fa-eye-slash', 'fa')}
    callback={props.togglePublication}
  >
    {props.resourceNode.meta.published &&
      <span className="label label-primary">
        {number(props.resourceNode.meta.views, true)}
      </span>
    }
  </PageAction>

PublishAction.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  togglePublication: T.func.isRequired
}

const FavoriteAction = props =>
  <PageAction
    type="callback"
    label={props.favorited ? 'You have favorited this resource. (click to remove it)' : 'You have not favorited this resource yet. (click to favorite it)'}
    icon={classes('fa', {
      'fa-star-o': !props.favorited,
      'fa-star': props.favorited
    })}
    callback={props.toggleFavorite}
  />

FavoriteAction.propTypes = {
  favorited: T.bool.isRequired,
  toggleFavorite: T.func.isRequired
}

const ManageRightsAction = props => {
  // computes simplified version of current rights
  const rights = getSimpleAccessRule(props.resourceNode.rights.all.permissions, props.resourceNode.workspace)
  const customRules = hasCustomRules(props.resourceNode.rights.all.permissions, props.resourceNode.workspace)

  let title, icon
  switch (rights) {
    case 'all':
      title = 'resource_rights_all_tip'
      icon = 'fa-unlock'
      break
    case 'user':
      title = 'resource_rights_user_tip'
      icon = 'fa-unlock'
      break
    case 'workspace':
      title = 'resource_rights_workspace_tip'
      icon = 'fa-unlock-alt'
      break
    case 'admin':
      title = 'resource_rights_admin_tip'
      icon = 'fa-lock'
      break
  }

  return (
    <PageAction
      type="modal"
      label={t_res(title)}
      icon={classes('fa', icon)}
      modal={[MODAL_RESOURCE_RIGHTS, {
        resourceNode: props.resourceNode,
        save: props.update
      }]}
    >
      {customRules &&
        <span className="fa fa-asterisk text-danger" />
      }
    </PageAction>
  )
}

ManageRightsAction.propTypes = {
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  update: T.func.isRequired
}

const LikeAction = props =>
  <PageAction
    type="callback"
    label="Like this resource"
    icon="fa fa-thumbs-o-up"
    callback={props.handleLike}
  >
    <span className="label label-primary">
      {props.likes}
    </span>
  </PageAction>

LikeAction.propTypes = {
  likes: T.number.isRequired,
  handleLike: T.func.isRequired
}

function getMoreActions(resourceNode, props) {
  const authenticatedUser = currentUser()

  return [
    // administrate
    {
      id: 'resource-edit-properties',
      type: 'modal',
      icon: 'fa fa-fw fa-pencil',
      label: t_res('edit-properties'),
      group: t_res('resource_management'),
      displayed: resourceNode.rights.current.administrate,
      modal: [MODAL_RESOURCE_PROPERTIES, {
        resourceNode: resourceNode,
        save: props.updateNode
      }]
    }, {
      id: 'resource-open-tracking',
      type: 'url',
      icon: 'fa fa-fw fa-line-chart',
      label: t_res('open-tracking'),
      group: t_res('resource_management'),
      displayed: resourceNode.rights.current.administrate,
      target: ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'open-tracking',
        node: resourceNode.autoId
      }]
    },
    // notifications
    {
      id: 'resource-toggle-notifications',
      type: 'callback',
      icon: 'fa fa-fw fa-bell-o',
      label: trans('follow', {}, 'actions'),
      group: t_res('resource_notifications'),
      displayed: authenticatedUser && !resourceNode.notifications.enabled,
      callback: () => props.toggleNotifications(resourceNode)
    }, {
      id: 'resource-toggle-notifications',
      type: 'callback',
      icon: 'fa fa-fw fa-bell',
      label: trans('unfollow', {}, 'actions'),
      group: t_res('resource_notifications'),
      displayed: authenticatedUser && resourceNode.notifications.enabled,
      callback: () => props.toggleNotifications(resourceNode)
    },
    // export
    {
      id: 'resource-export',
      type: 'url',
      icon: 'fa fa-fw fa-download',
      label: trans('export', {}, 'actions'),
      //group: trans('resource_notifications'),
      displayed: resourceNode.rights.current.export,
      target: ['claro_resource_action', {
        resourceType: resourceNode.meta.type,
        action: 'export',
        node: resourceNode.autoId
      }]
    }
  ]

  // TODO : grab custom actions from plugins
  /*<MenuItem
    key="resource-manage-tags"
    eventKey="resource-manage-tags"
  >
    <span className="fa fa-fw fa-tags" />
    Manage tags
  </MenuItem>,*/

  // TODO : create new action
  /*<MenuItem
    key="resource-show-as"
    eventKey="resource-show-as"
  >
    <span className="fa fa-fw fa-user-secret" />
    Show as...
  </MenuItem>,*/

  // TODO : grab custom actions from plugins
  /*<MenuItem
    key="resource-group-plugins"
    header={true}
  >
    Other
  </MenuItem>,*/

  /*<MenuItem
    key="resource-comments"
    eventKey="resource-comments"
  >
    <span className="fa fa-fw fa-comment" />
    Add a comment
  </MenuItem>,*/

  /*<MenuItem
    key="resource-notes"
    eventKey="resource-notes"
  >
    <span className="fa fa-fw fa-sticky-note" />
    Add a note
  </MenuItem>,*/

  // TODO : enable delete
  /*
  resourceNode.rights.current.delete &&
  <MenuItem key="resource-delete-divider" divider={true} />,

  resourceNode.rights.current.delete &&
  <MenuItem
    key="resource-delete"
    eventKey="resource-delete"
    className="dropdown-link-danger"
    onClick={e => {
      e.stopPropagation()
      props.showModal(MODAL_DELETE_CONFIRM, {
        title: t_res('delete'),
        question: t_res('delete_confirm_question'),
        handleConfirm: () => true
      })
    }}
  >
    <span className="fa fa-fw fa-trash" />
    {t_res('delete')}
  </MenuItem>*/
}

const ManagementGroup = props => {
  let editorOpened = false
  if (props.editor) {
    editorOpened = !!matchPath(props.location.pathname, {path: props.editor.path})
  }

  return (
    <PageGroupActions>
      {(props.editor && !editorOpened && props.resourceNode.rights.current.edit) &&
        <PageAction
          type="link"
          label={props.editor.label || t_res('edit')}
          icon={props.editor.icon || 'fa fa-pencil'}
          primary={true}
          target={props.editor.path}
        />
      }

      {(props.editor && editorOpened && props.resourceNode.rights.current.edit) &&
        <PageAction
          type="callback"
          label={t_res('save')}
          icon="fa fa-floppy-o"
          primary={true}
          disabled={props.editor.save.disabled}
          callback={props.editor.save.action}
        />
      }

      {props.resourceNode.rights.current.administrate &&
        <PublishAction
          resourceNode={props.resourceNode}
          togglePublication={() => props.togglePublication(props.resourceNode)}
        />
      }

      {props.resourceNode.rights.current.administrate &&
        <ManageRightsAction
          resourceNode={props.resourceNode}
          update={props.updateNode}
        />
      }
    </PageGroupActions>
  )
}

ManagementGroup.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired,
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,

  /**
   * If provided, this permits to manage the resource editor in the header (aka. open, save actions).
   */
  editor: T.shape({
    icon: T.string,
    label: T.string,
    opened: T.bool,
    path: T.string,
    open: T.oneOfType([T.func, T.string]),
    save: T.shape({
      disabled: T.bool.isRequired,
      action: T.oneOfType([T.string, T.func]).isRequired
    }).isRequired
  }),
  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired
}

const ManagementGroupActions = withRouter(ManagementGroup)

const CustomGroupActions = () =>
  <PageGroupActions>
    <FavoriteAction favorited={false} toggleFavorite={() => true} />
    <PageAction
      type="callback"
      label="Share this resource"
      icon="fa fa-share"
      callback={() => true}
    />
    <LikeAction likes={100} handleLike={() => true} />
  </PageGroupActions>

CustomGroupActions.propTypes = {

}

const ResourcePageActions = props => {
  const moreActions = [].concat(props.customActions, getMoreActions(props.resourceNode, props))

  return(
    <PageActions className="resource-actions">
      {(props.resourceNode.rights.current.edit || props.resourceNode.rights.current.administrate) &&
        <ManagementGroupActions
          resourceNode={props.resourceNode}
          editor={props.editor}
          togglePublication={props.togglePublication}
          updateNode={props.updateNode}
        />
      }

      {/*<CustomGroupActions />*/}

      <PageGroupActions>
        <FullScreenAction fullscreen={props.fullscreen} toggleFullscreen={props.toggleFullscreen} />

        {0 !== moreActions.length &&
          <MoreAction
            menuLabel={t_res(props.resourceNode.meta.type)}
            actions={moreActions}
          />
        }
      </PageGroupActions>
    </PageActions>
  )
}

ResourcePageActions.propTypes = {
  /**
   * The current ResourceNode.
   */
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,

  fullscreen: T.bool.isRequired,
  toggleFullscreen: T.func.isRequired,

  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired,
  toggleNotifications: T.func.isRequired,

  /**
   * If provided, this permits to manage the resource editor in the header (aka. open, save actions).
   */
  editor: T.shape({
    icon: T.string,
    label: T.string,
    opened: T.bool,
    path: T.string,
    open: T.oneOfType([T.func, T.string]),
    save: T.shape({
      disabled: T.bool.isRequired,
      action: T.oneOfType([T.string, T.func]).isRequired
    }).isRequired
  }),

  /**
   * Custom actions for the resources added by the UI.
   */
  customActions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

ResourcePageActions.defaultProps = {
  customActions: []
}

export {
  ResourcePageActions
}
