import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {hasPermission} from '#/main/app/security'
import {matchPath, withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {MODAL_BUTTON, ASYNC_BUTTON} from '#/main/app/buttons'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {Button} from '#/main/app/action/components/button'
import {ContentComments} from '#/main/app/content/components/comments'
import {selectors as resourceSelect} from '#/main/core/resource/store'

import {
  DropzoneType,
  DropType,
  Revision as RevisionType
} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/store/actions'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/modals/document'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const RevisionComponent = props => props.revision && props.drop ?
  <section className="resource-section revision-panel">
    <div className="revision-nav">

      {matchPath(props.location.pathname, {path: `${props.path}/revisions/`}) &&
        <Button
          className="btn-link btn-revision-nav"
          type={ASYNC_BUTTON}
          icon="fa fa-fw fa-chevron-left"
          label={trans('previous')}
          tooltip="right"
          request={{
            url: url(['claro_dropzone_revision_previous', {id: props.revision.id}]) + props.slideshowQueryString,
            success: (previous) => {
              if (previous && previous.id) {
                props.history.push(`${props.path}/revisions/${previous.id}`)
              }
            }
          }}
        />
      }

      <div className="revision-content">
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

        {matchPath(props.location.pathname, {path: `${props.path}/revisions/`}) &&
          <Button
            className="btn component-container"
            icon="fa fa-fw fa-plus"
            type={MODAL_BUTTON}
            label={trans('add_document', {}, 'dropzone')}
            modal={1 < props.dropzone.parameters.documents.length ?
              [MODAL_SELECTION, {
                icon: 'fa fa-fw fa-plus',
                title: trans('new_document', {}, 'dropzone'),
                items: props.dropzone.parameters.documents.map((type) => ({
                  name: type,
                  icon: constants.DOCUMENT_TYPE_ICONS[type],
                  label: constants.DOCUMENT_TYPES[type],
                  description: trans(`document_${type}_desc`, {}, 'dropzone')
                })),
                selectAction: (type) => ({
                  type: MODAL_BUTTON,
                  modal: [
                    MODAL_ADD_DOCUMENT, {
                      type: type.name,
                      save: (formData) => props.saveDocument(props.drop.id, props.revision.id, type.name, formData.data)
                    }
                  ]
                })
              }] :
              [MODAL_ADD_DOCUMENT, {
                type: props.dropzone.parameters.documents[0],
                save: (formData) => props.saveDocument(props.drop.id, props.revision.id, props.dropzone.parameters.documents[0], formData.data)
              }]}
          />
        }
      </div>

      {matchPath(props.location.pathname, {path: `${props.path}/revisions/`}) &&
        <Button
          className="btn-link btn-revision-nav"
          type={ASYNC_BUTTON}
          icon="fa fa-fw fa-chevron-right"
          label={trans('next')}
          tooltip="left"
          request={{
            url: url(['claro_dropzone_revision_next', {id: props.revision.id}])+props.slideshowQueryString,
            success: (next) => {
              if (next && next.id) {
                props.history.push(`${props.path}/revisions/${next.id}`)
              }
            }
          }}
        />
      }
    </div>

    <ContentComments
      title={trans('drop_comments', {}, 'dropzone')}
      currentUser={props.currentUser}
      comments={props.drop.comments}
      createComment={(comment) => props.saveDropComment(props.drop.id, comment)}
      editComment={(comment) => props.saveDropComment(props.drop.id, comment)}
    />

    <ContentComments
      title={trans('revision_comments', {}, 'dropzone')}
      currentUser={props.currentUser}
      comments={props.revision.comments}
      createComment={(comment) => props.saveRevisionComment(props.revision.id, comment)}
      updateComment={(comment) => props.saveRevisionComment(props.revision.id, comment)}
    />
  </section> :
  <div>
  </div>

RevisionComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
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
  saveDocument: T.func.isRequired,
  slideshowQueryString: T.string,
  history: T.object.isRequired
}

const Revision = withRouter(connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    isManager: hasPermission('edit', resourceSelect.resourceNode(state)),
    dropzone: selectors.dropzone(state),
    revision: selectors.revision(state),
    drop: selectors.currentDrop(state),
    slideshowQueryString: selectors.slideshowQueryString(state, selectors.STORE_NAME+'.revisions')
  }),
  (dispatch) => ({
    saveDropComment(dropId, comment) {
      dispatch(actions.saveDropComment(dropId, comment))
    },
    saveRevisionComment(revisionId, comment) {
      dispatch(actions.saveRevisionComment(revisionId, comment))
    },
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    saveDocument(dropId, revisionId, documentType, documentData) {
      dispatch(actions.saveManagerDocument(dropId, revisionId, documentType, documentData))
    },
    deleteDocument(documentId) {
      dispatch(actions.deleteManagerDocument(documentId))
    }
  })
)(RevisionComponent))

export {
  Revision
}
