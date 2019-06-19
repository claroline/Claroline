import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {hasPermission} from '#/main/app/security'
import {matchPath, withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {MODAL_RESOURCE_EXPLORER} from '#/main/core/modals/resources'

import {
  DropzoneType,
  DropType,
  Revision as RevisionType
} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/components/modal/add-document'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {Comments} from '#/plugin/drop-zone/resources/dropzone/player/components/comments'

const RevisionComponent = props => props.revision && props.drop ?
  <section className="resource-section revision-panel">
    <h2>{trans('revision', {}, 'dropzone')}</h2>

    <table className="revision-table table table-responsive table-bordered">
      <tbody>
        <tr>
          <th>{trans('creator')}</th>
          <td>{props.revision.creator ? `${props.revision.creator.firstName} ${props.revision.creator.lastName}` : trans('unknown')}</td>
        </tr>
        <tr>
          <th>{trans('creation_date')}</th>
          <td>{displayDate(props.revision.creationDate, false, true)}</td>
        </tr>
      </tbody>
    </table>

    <Documents
      documents={props.revision.documents}
      showMeta={true}
      isManager={props.isManager}
      {...props}
    />

    {matchPath(props.location.pathname, {path: '/revisions/'}) &&
      <CallbackButton
        className="btn pull-right"
        callback={() => props.addDocument(props.drop.id, props.revision.id, props.dropzone.parameters.documents)}
      >
        {trans('add_document', {}, 'dropzone')}
      </CallbackButton>
    }

    <hr className={matchPath(props.location.pathname, {path: '/revisions/'}) ? 'revision-comments-separator' : ''}/>

    <Comments
      comments={props.drop.comments}
      dropId={props.drop.id}
      title={trans('drop_comments', {}, 'dropzone')}
      saveComment={props.saveDropComment}
    />

    <hr className="revision-comments-separator"/>

    <Comments
      comments={props.revision.comments}
      revisionId={props.revision.id}
      title={trans('revision_comments', {}, 'dropzone')}
      saveComment={props.saveRevisionComment}
    />
  </section> :
  <div>
  </div>

RevisionComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }),
  isManager: T.bool,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  revision: T.shape(RevisionType.propTypes),
  drop: T.shape(DropType.propTypes),
  showModal: T.func.isRequired,
  saveDropComment: T.func.isRequired,
  saveRevisionComment: T.func.isRequired,
  addDocument: T.func.isRequired
}

const Revision = withRouter(connect(
  (state) => ({
    isManager: hasPermission('edit', resourceSelect.resourceNode(state)),
    dropzone: select.dropzone(state),
    revision: select.revision(state),
    drop: select.currentDrop(state)
  }),
  (dispatch) => ({
    saveDropComment(comment) {
      dispatch(actions.saveDropComment(comment))
    },
    saveRevisionComment(comment) {
      dispatch(actions.saveRevisionComment(comment))
    },
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    addDocument(dropId, revisionId, allowedDocuments) {
      dispatch(
        modalActions.showModal(MODAL_ADD_DOCUMENT, {
          allowedDocuments: allowedDocuments,
          save: (data) => dispatch(actions.saveManagerDocument(dropId, revisionId, data.type, data.data)),
          pickResource: (data) => {
            let title
            let callback
            title = trans('add_primary_resource', {}, 'path')
            callback = (selected) => {dispatch(actions.saveManagerDocument(dropId, revisionId, data.type, selected[0].id))}
            dispatch(modalActions.showModal(MODAL_RESOURCE_EXPLORER, {
              title: title,
              selectAction: (selected) => ({
                type: 'callback',
                label: trans('select', {}, 'actions'),
                callback: () => callback(selected)
              })
            }))
          }
        })
      )
    },
    deleteDocument(documentId) {
      dispatch(actions.deleteManagerDocument(documentId))
    }
  })
)(RevisionComponent))

export {
  Revision
}
