import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import ButtonToolbar from 'react-bootstrap/lib/ButtonToolbar'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {Button} from '#/main/app/action/components/button'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {ResourceNode as ResourceNodeType} from '#/main/core/resource/prop-types'
import {HtmlText} from '#/main/core/layout/components/html-text'

import {DropzoneType, DropType, Revision as RevisionType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/components/modal/add-document'
import {MODAL_CORRECTION} from '#/plugin/drop-zone/resources/dropzone/correction/components/modal/correction-modal'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {Comments} from '#/plugin/drop-zone/resources/dropzone/player/components/comments'

const getTitle = (dropzone, correction, index) => {
  let title = ''

  if (dropzone.display.correctorDisplayed) {
    if (dropzone.parameters.dropType === constants.DROP_TYPE_TEAM) {
      title = trans('correction_from', {name: correction.teamName}, 'dropzone')
    } else {
      title = trans('correction_from', {name: `${correction.user.firstName} ${correction.user.lastName}`}, 'dropzone')
    }
  } else {
    title = trans('correction_n', {number: index}, 'dropzone')
  }

  return title
}

const Corrections = props =>
  <table className="table corrections-table">
    <thead>
      <tr>
        <th></th>
        <th></th>
        <th>{trans('start_date', {}, 'platform')}</th>
        <th>{trans('end_date', {}, 'platform')}</th>
        {props.dropzone.display.showScore &&
          <th>{trans('score', {}, 'platform')}</th>
        }
      </tr>
    </thead>
    <tbody>
      {props.corrections
        .filter(c => c.finished)
        .map((c, idx) =>
          <tr key={`correction-row-${c.id}`}>
            <td>
              {c.correctionDenied &&
              <span className="fa fa-fw fa-exclamation-triangle" />
              }
            </td>
            <td>
              <a
                className="pointer-hand"
                onClick={() => {
                  props.showModal(MODAL_CORRECTION, {
                    title: getTitle(props.dropzone, c, idx + 1),
                    correction: c,
                    dropzone: props.dropzone,
                    showDenialBox: props.dropzone.parameters.correctionDenialEnabled,
                    denyCorrection: (correctionId, comment) => props.denyCorrection(correctionId, comment)
                  })
                }}
              >

                {getTitle(props.dropzone, c, idx + 1)}
              </a>
            </td>
            <td>{c.startDate}</td>
            <td>{c.endDate}</td>
            {props.dropzone.display.showScore &&
              <td>{c.score} / {props.dropzone.parameters.scoreMax}</td>
            }
          </tr>
        )
      }
    </tbody>
  </table>

Corrections.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  corrections: T.array,
  denyCorrection: T.func,
  showModal: T.func
}

const MyDropComponent = props =>
  <section className="resource-section drop-panel">
    <h2>{trans('my_drop', {}, 'dropzone')}</h2>
    {props.dropzone.instruction &&
      <HtmlText>{props.dropzone.instruction}</HtmlText>
    }

    <Documents
      documents={props.myDrop.documents}
      canEdit={props.isDropEnabled && !props.myDrop.finished}
      showUser={props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM}
      {...props}
    />

    {props.dropzone.display.displayCorrectionsToLearners && props.myDrop.finished && props.myDrop.corrections.filter(c => c.finished).length > 0 &&
      <Corrections
        corrections={props.myDrop.corrections.filter(c => c.finished && c.valid)}
        {...props}
      />
    }

    {props.isDropEnabled && !props.myDrop.finished &&
      <div className="text-right">
        <ButtonToolbar className="pull-right">
          {props.dropzone.parameters.revisionEnabled &&
            <Button
              type={CALLBACK_BUTTON}
              icon={'fa fa-fw fa-comments-o icon-with-text-right'}
              label= {trans('submit_for_revision', {}, 'dropzone')}
              className="btn"
              disabled={!props.myDrop.documents || 0 === props.myDrop.documents.filter(d => !d.revision).length}
              callback={() => props.submitForRevision(props.myDrop.id)}
            />
          }
          {props.dropzone.parameters.revisionEnabled &&
            <Button
              type={LINK_BUTTON}
              icon={'fa fa-fw fa-history icon-with-text-right'}
              label={trans('revisions_history', {}, 'dropzone')}
              className="btn"
              target={`${props.path}/my/drop/revisions`}
            />
          }
          <Button
            type={CALLBACK_BUTTON}
            icon={'fa fa-fw fa-plus icon-with-text-right'}
            label= {trans('add_document', {}, 'dropzone')}
            className="btn btn-default"
            callback={() => props.addDocument(props.myDrop.id, props.dropzone.parameters.documents, props.resourceNode.parent)}
          />
          <Button
            type={CALLBACK_BUTTON}
            icon={'fa fa-fw fa-upload icon-with-text-right'}
            label= {trans('submit_my_drop', {}, 'dropzone')}
            className="btn primary"
            disabled={!props.myDrop.documents || 0 === props.myDrop.documents.length}
            callback={() => props.submit(props.myDrop.id)}
          />
        </ButtonToolbar>
      </div>
    }

    {props.isDropEnabled && !props.myDrop.finished && props.dropzone.parameters.revisionEnabled &&
      <hr className="revision-comments-separator"/>
    }

    {props.isDropEnabled && !props.myDrop.finished && props.dropzone.parameters.revisionEnabled &&
      <Comments
        comments={props.myDrop.comments}
        dropId={props.myDrop.id}
        title={trans('drop_comments', {}, 'dropzone')}
        saveComment={props.saveDropComment}
        currentUser={props.currentUser}
      />
    }

    {props.isDropEnabled && !props.myDrop.finished && props.currentRevisionId && props.revision &&
      <hr className="revision-comments-separator"/>
    }

    {props.isDropEnabled && !props.myDrop.finished && props.currentRevisionId && props.revision &&
      <Comments
        comments={props.revision.comments}
        revisionId={props.currentRevisionId}
        title={trans('revision_comments', {}, 'dropzone')}
        saveComment={props.saveRevisionComment}
        currentUser={props.currentUser}
      />
    }
  </section>

MyDropComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  myDrop: T.shape(DropType.propTypes).isRequired,
  isDropEnabled: T.bool.isRequired,
  currentRevisionId: T.string,
  revision: T.shape(RevisionType.propTypes),
  resourceNode: T.shape(ResourceNodeType.propTypes),
  submit: T.func.isRequired,
  denyCorrection: T.func.isRequired,
  showModal: T.func.isRequired,
  addDocument: T.func.isRequired,
  saveDocument: T.func.isRequired,
  submitForRevision: T.func.isRequired,
  saveRevisionComment: T.func.isRequired,
  saveDropComment: T.func.isRequired
}

const MyDrop = connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    dropzone: selectors.dropzone(state),
    myDrop: selectors.myDrop(state),
    isDropEnabled: selectors.isDropEnabled(state),
    currentRevisionId: selectors.currentRevisionId(state),
    revision: selectors.revision(state),
    resourceNode: resourceSelect.resourceNode(state)
  }),
  (dispatch) => ({
    saveDocument: (dropType, dropData) => dispatch(actions.saveDocument(dropType, dropData)),
    addDocument(dropId, allowedDocuments) {
      dispatch(
        modalActions.showModal(MODAL_ADD_DOCUMENT, {
          allowedDocuments: allowedDocuments,
          save: (data) => dispatch(actions.saveDocument(dropId, data.type, data.data))
        })
      )
    },
    deleteDocument(documentId) {
      dispatch(actions.deleteDocument(documentId))
    },
    submit(id) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-exclamation-triangle',
          title: trans('final_drop', {}, 'dropzone'),
          question: trans('submit_my_drop_confirm', {}, 'dropzone'),
          confirmButtonText: trans('to_drop', {}, 'dropzone'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.submitDrop(id))
        })
      )
    },
    submitForRevision(id) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-exclamation-triangle',
          title: trans('submit_for_revision', {}, 'dropzone'),
          question: trans('submit_for_revision_confirm', {}, 'dropzone'),
          confirmButtonText: trans('submit_for_revision', {}, 'dropzone'),
          handleConfirm: () => dispatch(actions.submitDropForRevision(id))
        })
      )
    },
    denyCorrection: (correctionId, comment) => dispatch(correctionActions.denyCorrection(correctionId, comment)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props)),
    saveRevisionComment(comment) {
      dispatch(actions.saveRevisionComment(comment))
    },
    saveDropComment(comment) {
      dispatch(actions.saveDropComment(comment, true))
    }
  })
)(MyDropComponent)

export {
  MyDrop
}
