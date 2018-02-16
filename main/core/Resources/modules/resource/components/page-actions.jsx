import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/*import {generateUrl} from '#/main/core/api/router'*/
import {matchPath, withRouter} from '#/main/core/router'
import {t_res} from '#/main/core/resource/translation'

import {getSimpleAccessRule, hasCustomRules} from '#/main/core/resource/rights'

/*import {MODAL_DELETE_CONFIRM}      from '#/main/core/layout/modal'*/
import {MODAL_RESOURCE_PROPERTIES} from '#/main/core/resource/components/modal/edit-properties.jsx'
import {MODAL_RESOURCE_RIGHTS}     from '#/main/core/resource/components/modal/edit-rights.jsx'

import {
  PageActions,
  PageGroupActions,
  PageAction,
  FullScreenAction,
  MoreAction
} from '#/main/core/layout/page/components/page-actions.jsx'

const PublishAction = props =>
  <PageAction
    id="resource-publish"
    title={t_res(props.published ? 'resource_unpublish' : 'resource_publish')}
    icon={classes(props.published ? 'fa-eye' : 'fa-eye-slash', 'fa')}
    action={props.togglePublication}
  />

PublishAction.propTypes = {
  published: T.bool.isRequired,
  togglePublication: T.func.isRequired
}

const FavoriteAction = props =>
  <PageAction
    id="resource-favorite"
    title={props.favorited ? 'You have favorited this resource. (click to remove it)' : 'You have not favorited this resource yet. (click to favorite it)'}
    icon={classes('fa', {
      'fa-star-o': !props.favorited,
      'fa-star': props.favorited
    })}
    action={props.toggleFavorite}
  />

FavoriteAction.propTypes = {
  favorited: T.bool.isRequired,
  toggleFavorite: T.func.isRequired
}

const ManageRightsAction = props => {
  let title, icon
  switch (props.rights) {
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
      id="resource-rights"
      title={t_res(title)}
      icon={classes('fa', icon)}
      action={props.openRightsManagement}
    >
      {props.customRules &&
        <span className="fa fa-asterisk text-danger" />
      }
    </PageAction>
  )
}

ManageRightsAction.propTypes = {
  rights: T.oneOf(['all', 'admin', 'user', 'workspace']).isRequired,
  customRules: T.bool,
  openRightsManagement: T.func.isRequired
}

ManageRightsAction.defaultProps = {
  customRules: false
}

const LikeAction = props =>
  <PageAction
    id="resource-like"
    title="Like this resource"
    icon="fa fa-thumbs-o-up"
    action={props.handleLike}
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
  return [
    // edit resource properties
    {
      icon: 'fa fa-fw fa-pencil',
      label: t_res('edit-properties'),
      group: t_res('resource_management'),
      displayed: resourceNode.rights.current.administrate,
      action: () => props.showModal(MODAL_RESOURCE_PROPERTIES, {
        resourceNode: resourceNode,
        save: props.updateNode
      })
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

  // TODO : enable tracking. It can't be enabled because for now resource actions wants the ID of the node
  // and we only have its UUID
  /*<MenuItem
    key="resource-log"
    eventKey="resource-log"
  >
    <span className="fa fa-fw fa-line-chart" />
    {t_res('open-tracking')}
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

  // TODO : enable delete and export. It can't be enabled because for now resource actions wants the ID of the node
  // and we only have its UUID
  /*resourceNode.rights.current.export &&
  <MenuItem key="resource-export-divider" divider={true} />,

  resourceNode.rights.current.export &&
  <MenuItem
    key="resource-export"
    eventKey="resource-export"
    href={generateUrl('claro_resource_action', {
      resourceType: resourceNode.meta.type,
      action: 'export',
      node: resourceNode.id
    })}
  >
    <span className="fa fa-fw fa-upload" />
    {t_res('export')}
  </MenuItem>,

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
  let editorOpen

  if (props.editor) {
    if (props.editor.path) {
      // routed editor
      editorOpened = !!matchPath(props.location.pathname, {path: props.editor.path})
      editorOpen = '#'+props.editor.path
    } else {
      // for retro compatibility (all resource editor should be routed)
      editorOpened = props.editor.opened
      editorOpen = props.editor.open
    }
  }


  return (
    <PageGroupActions>
      {(props.editor && !editorOpened && props.resourceNode.rights.current.edit) &&
        <PageAction
          id="resource-edit"
          title={props.editor.label || t_res('edit')}
          icon={props.editor.icon || 'fa fa-pencil'}
          primary={true}
          action={editorOpen}
        />
      }

      {(props.editor && editorOpened && props.resourceNode.rights.current.edit) &&
        <PageAction
          id="resource-save"
          title={t_res('save')}
          icon="fa fa-floppy-o"
          primary={true}
          disabled={props.editor.save.disabled}
          action={props.editor.save.action}
        />
      }

      {props.resourceNode.rights.current.administrate &&
        <PublishAction
          published={props.resourceNode.meta.published}
          togglePublication={() => props.togglePublication(props.resourceNode)}
        />
      }

      {props.resourceNode.rights.current.administrate &&
        <ManageRightsAction
          rights={getSimpleAccessRule(props.resourceNode.rights.all.permissions, props.resourceNode.workspace)}
          customRules={hasCustomRules(props.resourceNode.rights.all.permissions, props.resourceNode.workspace)}
          openRightsManagement={() => props.showModal(MODAL_RESOURCE_RIGHTS, {
            resourceNode: props.resourceNode,
            save: props.updateNode
          })}
        />
      }
    </PageGroupActions>
  )
}

ManagementGroup.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired,
  resourceNode: T.shape({
    workspace: T.object,
    meta: T.shape({
      published: T.bool.isRequired
    }).isRequired,
    rights: T.shape({
      current: T.shape({
        edit: T.bool,
        administrate: T.bool,
        export: T.bool,
        delete: T.bool
      }).isRequired,
      all: T.shape({
        permissions: T.object.isRequired
      }).isRequired
    })
  }).isRequired,
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
  updateNode: T.func.isRequired,
  showModal: T.func.isRequired
}

const ManagementGroupActions = withRouter(ManagementGroup)

const CustomGroupActions = () =>
  <PageGroupActions>
    <FavoriteAction favorited={false} toggleFavorite={() => true} />
    <PageAction id="resource-share" title="Share this resource" icon="fa fa-share" action="#share" />
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
          showModal={props.showModal}
        />
      }

      {/*<CustomGroupActions />*/}

      <PageGroupActions>
        <FullScreenAction fullscreen={props.fullscreen} toggleFullscreen={props.toggleFullscreen} />

        {0 !== moreActions.length &&
          <MoreAction
            id="resource-more"
            title={t_res(props.resourceNode.meta.type)}
            actions={moreActions}
          />
        }
      </PageGroupActions>
    </PageActions>
  )
}

ResourcePageActions.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired,
    description: T.string,
    workspace: T.object,
    meta: T.shape({
      type: T.string.isRequired,
      published: T.bool.isRequired
    }).isRequired,
    rights: T.shape({
      current: T.shape({
        edit: T.bool,
        administrate: T.bool,
        export: T.bool,
        delete: T.bool
      }),
      all: T.object
    })
  }).isRequired,

  fullscreen: T.bool.isRequired,
  toggleFullscreen: T.func.isRequired,
  showModal: T.func.isRequired,

  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired,

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
  customActions: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    disabled: T.bool,
    displayed: T.bool,
    action: T.oneOfType([T.string, T.func]).isRequired,
    dangerous: T.bool,
    group: T.string
  }))
}

ResourcePageActions.defaultProps = {
  customActions: []
}

export {
  ResourcePageActions
}
