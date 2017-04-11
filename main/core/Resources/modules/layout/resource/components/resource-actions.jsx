import React, { PropTypes as T } from 'react'
import classes from 'classnames'
import MenuItem from 'react-bootstrap/lib/MenuItem'

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
    title={props.published ? 'This resource is published. (click to unpublish it)' : 'This resource is not published. (click to publish it)'}
    icon={classes('fa', {
      'fa-eye-slash': !props.published,
      'fa-eye': props.published
    })}
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
      title = 'This resource is accessible by everyone. (click to edit access rights)'
      icon = 'fa-unlock'
      break
    case 'admin':
      title = 'This resource is accessible by managers only. (click to edit access rights)'
      icon = 'fa-lock'
      break
    case 'workspace':
      title = 'This resource is accessible by workspace users. (click to edit access rights)'
      icon = 'fa-unlock-alt'
      break
    case 'custom':
      title = 'This resource has custom access rights. (click to edit access rights)'
      icon = 'fa-unlock-alt'
      break
  }

  return (
    <PageAction
      id="resource-rights"
      title={title}
      icon={classes('fa', icon)}
      action={props.openRightsManagement}
    >
      {'custom' === props.rights &&
        <span className="fa fa-asterisk text-danger" />
      }
    </PageAction>
  )
}

ManageRightsAction.propTypes = {
  rights: T.oneOf(['all', 'admin', 'workspace', 'custom']).isRequired,
  openRightsManagement: T.func.isRequired
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

/*function getMoreActions(resourceNode) {
  return [
    <MenuItem header>Management</MenuItem>,

    <MenuItem
      eventKey="resource-edit-props"
    >
      <span className="fa fa-fw fa-pencil" />
      Edit properties
    </MenuItem>,

    <MenuItem eventKey="6">
      <span className="fa fa-fw fa-desktop" />
      Edit display options
    </MenuItem>,

    <MenuItem eventKey="7">
      <span className="fa fa-fw fa-tags" />
      Manage tags
    </MenuItem>,

    <MenuItem eventKey="5">
      <span className="fa fa-fw fa-line-chart" />
      Show tracking
    </MenuItem>,

    <MenuItem eventKey="8">
      <span className="fa fa-fw fa-user-secret" />
      Show as...
    </MenuItem>,

    <MenuItem header>Other</MenuItem>,

    <MenuItem eventKey="9">
      <span className="fa fa-fw fa-comment" />
      Add a comment
    </MenuItem>,

    <MenuItem eventKey="10">,

    <span className="fa fa-fw fa-sticky-note" />
      Add a note
    </MenuItem>,

    resourceNode.meta.exportable &&
    <MenuItem divider/>,

    resourceNode.meta.exportable &&
    <MenuItem eventKey="resource-export">
      <span className="fa fa-fw fa-upload" />
      Export resource
    </MenuItem>,

    resourceNode.meta.deletable &&
    <MenuItem divider/>,

    resourceNode.meta.deletable &&
    <MenuItem eventKey="resource-delete" className="dropdown-link-danger">
      <span className="fa fa-fw fa-trash" />
      Delete resource
    </MenuItem>
  ]
}*/

/**
 * @param props
 * @constructor
 */
const ResourceActions = props =>
  <PageActions className="resource-actions">
    {props.resourceNode.meta.editable &&
      <PageGroupActions>
        {!props.editMode &&
          <PageAction id="resource-edit" title="Edit this resource" icon="fa fa-pencil" primary={true} action={props.edit} />
        }

        {props.editMode &&
          <PageAction
            id="resource-save"
            title="Save your modifications"
            icon="fa fa-floppy-o"
            primary={true}
            disabled={props.save.disabled}
            action={props.save.action}
          />
        }

        {/*<PublishAction published={props.resourceNode.meta.published} togglePublication={props.togglePublication} />
        <ManageRightsAction rights="workspace" openRightsManagement={() => true} />*/}
      </PageGroupActions>
    }

    {/*<PageGroupActions>
      <FavoriteAction favorited={false} toggleFavorite={() => true} />
      <PageAction id="resource-share" title="Share this resource" icon="fa fa-share" action="#share" />
      <LikeAction likes={100} handleLike={() => true} />
    </PageGroupActions>*/}

    <PageGroupActions>
      <FullScreenAction fullscreen={props.fullscreen} toggleFullscreen={props.toggleFullscreen} />
      <MoreAction id="resource-more">
        <MenuItem header>Quiz</MenuItem>
        {props.customActions.map((customAction, index) =>
          React.createElement(MenuItem, {
            key: `resource-more-action-${index}`,
            eventKey: `resource-action-${index}`,
            children: [
              <span className={customAction.icon} />,
              customAction.label
            ],
            [typeof customAction.action === 'function' ? 'onClick' : 'href']: customAction.action
          })
        )}

        {/*{getMoreActions(props.resourceNode)}*/}
      </MoreAction>
    </PageGroupActions>
  </PageActions>

ResourceActions.propTypes = {
  resourceNode: T.shape({
    meta: T.shape({
      published: T.bool.isRequired,
      editable: T.bool.isRequired,
      deletable: T.bool.isRequired,
      exportable: T.bool.isRequired
    }).isRequired
  }).isRequired,
  fullscreen: T.bool.isRequired,
  toggleFullscreen: T.func.isRequired,
  togglePublication: T.func.isRequired,
  showModal: T.func.isRequired,

  editMode: T.bool,
  edit: T.oneOfType([T.func, T.string]).isRequired,
  save: T.shape({
    disabled: T.bool.isRequired,
    action: T.oneOfType([T.string, T.func]).isRequired
  }).isRequired,
  customActions: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    label: T.string.isRequired,
    disabled: T.bool,
    action: T.oneOfType([T.string, T.func]).isRequired
  })).isRequired
}

export {
  ResourceActions
}
