import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {hasPermission} from '#/main/app/security'
import {matchPath, withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {MODAL_BUTTON} from '#/main/app/buttons'
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
import {ResourcePage} from '#/main/core/resource'

const RevisionComponent = props => props.revision && props.drop ?
  <ResourcePage>
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
  </ResourcePage> :
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
  saveDropComment: T.func.isRequired,
  saveRevisionComment: T.func.isRequired,
  saveDocument: T.func.isRequired,
  history: T.object.isRequired
}

const Revision = withRouter(connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    isManager: hasPermission('edit', resourceSelect.resourceNode(state)),
    dropzone: selectors.dropzone(state),
    revision: selectors.revision(state),
    drop: selectors.currentDrop(state)
  }),
  (dispatch) => ({
    saveDropComment(dropId, comment) {
      dispatch(actions.saveDropComment(dropId, comment))
    },
    saveRevisionComment(revisionId, comment) {
      dispatch(actions.saveRevisionComment(revisionId, comment))
    },
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
