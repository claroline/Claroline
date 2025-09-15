import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_CONTENT} from '#/plugin/exo/contents/modals'
import {getContentDefinition} from '#/plugin/exo/contents/utils'

const MediaThumbnailActions = props =>
  <span className="content-thumbnail-actions">
    {props.hasEditBtn &&
      <span
        role="button"
        title={trans('edit')}
        className="action-button fa fa-pencil"
        onClick={e => props.handleEdit(e)}
      />
    }
    {props.hasDeleteBtn &&
      <span
        role="button"
        title={trans('delete')}
        className="action-button fa fa-trash"
        onClick={e => props.handleDelete(e)}
      />
    }
  </span>

MediaThumbnailActions.propTypes = {
  hasDeleteBtn: T.bool,
  hasEditBtn: T.bool,
  handleEdit: T.func,
  handleDelete: T.func
}

const MediaThumbnailComponent = props =>
  <div
    className={classes('content-thumbnail', {
      'active': props.active
    })}
    onClick={() => props.showModal(MODAL_CONTENT, {
      data: props.data,
      type: props.type
    })}
    role="button"
  >
    <span className="content-thumbnail-topbar">
      <MediaThumbnailActions
        hasDeleteBtn={props.canDelete}
        hasEditBtn={props.canEdit}
        handleEdit={props.handleEdit}
        handleDelete={props.handleDelete}
      />
    </span>
    <span className="content-thumbnail-content">
      {createElement(getContentDefinition(props.type).thumbnail, {
        data: props.data,
        type: props.type
      })}
    </span>
  </div>

MediaThumbnailComponent.propTypes = {
  id: T.string.isRequired,
  data: T.string,
  type: T.string.isRequired,
  active: T.bool,
  canDelete: T.bool,
  canEdit: T.bool,
  handleEdit: T.func,
  handleDelete: T.func,
  showModal: T.func.isRequired
}

const MediaThumbnail = connect(
  null,
  (dispatch) => ({
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  })
)(MediaThumbnailComponent)

export {
  MediaThumbnail
}
