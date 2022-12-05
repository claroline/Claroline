import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'

import {MODAL_CONTENT} from '#/plugin/exo/contents/modals'
import {makeSortable} from '#/plugin/exo/utils/sortable'
import {getContentDefinition} from '#/plugin/exo/contents/utils'
import {ContentThumbnailDragPreview} from '#/plugin/exo/contents/components/content-thumbnail-drag-preview'

const Actions = props =>
  <span className="content-thumbnail-actions">
    {props.hasExpandBtn &&
      <span
        role="button"
        title={trans('watch_at_the_original_size', {}, 'quiz')}
        className="action-button fa fa-external-link"
        onClick={e => {
          e.stopPropagation()
          props.handleExpand(e)
        }}
      />
    }
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
    {props.hasSortBtn && props.connectDragSource(
      <span
        role="button"
        title={trans('move')}
        className="action-button fa fa-arrows drag-handle"
        draggable="true"
      />
    )}
  </span>

Actions.propTypes = {
  connectDragSource: T.func,
  hasDeleteBtn: T.bool,
  hasEditBtn: T.bool,
  hasSortBtn: T.bool,
  hasExpandBtn: T.bool,
  handleEdit: T.func,
  handleDelete: T.func,
  handleExpand: T.func
}

let ContentThumbnail = props => props.connectDropTarget(
  <span
    className={classes('content-thumbnail', {'active': props.active})}
    style={{opacity: props.isDragging ? 0 : 1}}
    onClick={() => {
      props.showModal(MODAL_CONTENT, {
        data: props.data,
        type: props.type
      })
    }}
  >
    <span className="content-thumbnail-topbar">
      <Actions
        hasDeleteBtn={props.canDelete}
        hasEditBtn={props.canEdit}
        hasSortBtn={props.canSort}
        hasExpandBtn={getContentDefinition(props.type).type === 'video'}
        handleEdit={props.handleEdit}
        handleDelete={props.handleDelete}
        handleExpand={() => {
          props.showModal(MODAL_CONTENT, {
            data: props.data,
            type: props.type
          })
        }}
        {...props}
      />
    </span>
    <span className="content-thumbnail-content">
      {React.createElement(
        getContentDefinition(props.type).thumbnail,
        {data: props.data, type: props.type}
      )}
    </span>
  </span>
)

ContentThumbnail.propTypes = {
  id: T.string.isRequired,
  index: T.number.isRequired,
  data: T.string,
  type: T.string.isRequired,
  active: T.bool,
  canDelete: T.bool,
  canEdit: T.bool,
  canSort: T.bool,
  onSort: T.func,
  handleEdit: T.func,
  handleDelete: T.func,
  sortDirection: T.string,
  validating: T.bool,
  hasErrors: T.bool,
  showModal: T.func.isRequired,
  connectDragPreview: T.func.isRequired,
  connectDragSource: T.func.isRequired,
  connectDropTarget: T.func.isRequired
}

ContentThumbnail = makeSortable(
  ContentThumbnail,
  'CONTENT_THUMBNAIL',
  ContentThumbnailDragPreview
)

ContentThumbnail = connect(
  null,
  (dispatch) => ({
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  })
)(ContentThumbnail)

export {
  ContentThumbnail
}
