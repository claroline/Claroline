import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import MenuItem from 'react-bootstrap/lib/MenuItem'

/*import {generateUrl} from '#/main/core/fos-js-router'*/
import {t_res} from '#/main/core/layout/resource/translation'

import {getSimpleAccessRule, hasCustomRules} from '#/main/core/layout/resource/rights/utils'

/*import {MODAL_DELETE_CONFIRM}      from '#/main/core/layout/modal'*/
import {MODAL_RESOURCE_PROPERTIES} from '#/main/core/layout/resource/components/modal/edit-properties.jsx'
import {MODAL_RESOURCE_RIGHTS}     from '#/main/core/layout/resource/rights/components/modal/edit-rights.jsx'

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
  rights: T.oneOf(['all', 'admin', 'workspace']).isRequired,
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
    resourceNode.rights.current.administrate &&
    <MenuItem
      key="resource-group-management"
      header={true}
    >
      {t_res('resource_management')}
    </MenuItem>,

    resourceNode.rights.current.administrate &&
    <MenuItem
      key="resource-edit-props"
      eventKey="resource-edit-props"
      onClick={() => props.showModal(MODAL_RESOURCE_PROPERTIES, {
        resourceNode: resourceNode,
        save: props.updateNode
      })}
    >
      <span className="fa fa-fw fa-pencil" />
      {t_res('edit-properties')}
    </MenuItem>/*,*/

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
  ]
}

const ManagementGroupActions = props =>
  <PageGroupActions>
    {(props.editor && !props.editor.opened && props.resourceNode.rights.current.edit) &&
      <PageAction
        id="resource-edit"
        title={t_res('edit')}
        icon="fa fa-pencil"
        primary={true}
        action={props.editor.open}
      />
    }

    {(props.editor && props.editor.opened && props.resourceNode.rights.current.edit) &&
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

ManagementGroupActions.propTypes = {
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
    opened: T.bool,
    open: T.oneOfType([T.func, T.string]).isRequired,
    save: T.shape({
      disabled: T.bool.isRequired,
      action: T.oneOfType([T.string, T.func]).isRequired
    }).isRequired
  }),
  togglePublication: T.func.isRequired,
  updateNode: T.func.isRequired,
  showModal: T.func.isRequired
}

const CustomGroupActions = () =>
  <PageGroupActions>
    <FavoriteAction favorited={false} toggleFavorite={() => true} />
    <PageAction id="resource-share" title="Share this resource" icon="fa fa-share" action="#share" />
    <LikeAction likes={100} handleLike={() => true} />
  </PageGroupActions>

CustomGroupActions.propTypes = {

}

/**
 * @param props
 * @constructor
 */
const ResourceActions = props =>
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
        <MoreAction id="resource-more">
          {props.customActions && 0 !== props.customActions.length &&
            <MenuItem
              key="resource-group-type"
              header={true}
            >
              {t_res(props.resourceNode.meta.type)}
            </MenuItem>
          }

          {props.customActions && 0 !== props.customActions.length &&
            props.customActions.map((customAction, index) =>
              React.createElement(MenuItem, {
                key: `resource-more-action-${index}`,
                eventKey: `resource-action-${index}`,
                children: [
                  <span className={customAction.icon} />,
                  customAction.label
                ],
                [typeof customAction.action === 'function' ? 'onClick' : 'href']: customAction.action
              })
            )
          }

          {getMoreActions(props.resourceNode, props)}
        </MoreAction>
    </PageGroupActions>
  </PageActions>

ResourceActions.propTypes = {
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
      all: T.object.isRequired
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
    opened: T.bool,
    open: T.oneOfType([T.func, T.string]).isRequired,
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
    action: T.oneOfType([T.string, T.func]).isRequired
  }))
}

export {
  ResourceActions
}
