import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, displayDate} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {ContentComments} from '#/main/app/content/components/comments'
import {ContentTitle} from '#/main/app/content/components/title'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {MODAL_ALERT} from '#/main/app/modals/alert'

import {DropzoneType, DropType, Revision as RevisionType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {MODAL_CORRECTION} from '#/plugin/drop-zone/resources/dropzone/correction/components/modal/correction-modal'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/modals/document'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'

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
            <td>{displayDate(c.startDate, false, true)}</td>
            <td>{displayDate(c.endDate, false, true)}</td>
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

const MyDrop = props =>
  <section className="resource-section">
    <ContentTitle
      level={2}
      title={trans('my_drop', {}, 'dropzone')}
      actions={[
        {
          name: 'show-instructions',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('show_instructions', {}, 'dropzone'),
          modal: [MODAL_ALERT, {
            title: trans('instructions', {}, 'dropzone'),
            message: props.dropzone.instruction
          }]
        }
      ]}
    />

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
      <Toolbar
        className="component-container"
        buttonName="btn"
        actions={[
          {
            name: 'request-revision',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-comments-o',
            label: trans('submit_for_revision', {}, 'dropzone'),
            displayed: props.dropzone.parameters.revisionEnabled,
            disabled: !props.myDrop.documents || 0 === props.myDrop.documents.filter(d => !d.revision).length,
            callback: () => props.submitForRevision(props.myDrop.id)
          }, {
            name: 'revision-history',
            type: LINK_BUTTON,
            icon: 'fa fa-fw fa-history',
            label: trans('revisions_history', {}, 'dropzone'),
            target: `${props.path}/my/drop/revisions`,
            displayed: props.dropzone.parameters.revisionEnabled
          }, {
            name: 'add-document',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_document', {}, 'dropzone'),
            modal: 1 < props.dropzone.parameters.documents.length ?
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
                      save: (formData) => props.saveDocument(props.myDrop.id, type.name, formData.data)
                    }
                  ]
                })
              }] :
              [MODAL_ADD_DOCUMENT, {
                type: props.dropzone.parameters.documents[0],
                save: (formData) => props.saveDocument(props.myDrop.id, props.dropzone.parameters.documents[0], formData.data)
              }]
          }, {
            name: 'finish',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-upload',
            label: trans('submit_my_drop', {}, 'dropzone'),
            callback: () => props.submit(props.myDrop.id),
            disabled: !props.myDrop.documents || 0 === props.myDrop.documents.length,
            primary: true
          }
        ]}
      />
    }

    {props.isDropEnabled && !props.myDrop.finished && props.dropzone.parameters.revisionEnabled &&
      <ContentComments
        title={trans('drop_comments', {}, 'dropzone')}
        currentUser={props.currentUser}
        comments={props.myDrop.comments}
        createComment={(comment) => props.saveDropComment(props.myDrop.id, comment)}
        editComment={(comment) => props.saveDropComment(props.myDrop.id, comment)}
      />
    }

    {props.isDropEnabled && !props.myDrop.finished && props.currentRevisionId && props.revision &&
      <ContentComments
        title={trans('revision_comments', {}, 'dropzone')}
        currentUser={props.currentUser}
        comments={props.revision.comments}
        createComment={(comment) => props.saveRevisionComment(props.currentRevisionId, comment)}
        updateComment={(comment) => props.saveRevisionComment(props.currentRevisionId, comment)}
      />
    }
  </section>

MyDrop.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  myDrop: T.shape(DropType.propTypes).isRequired,
  isDropEnabled: T.bool.isRequired,
  currentRevisionId: T.string,
  revision: T.shape(RevisionType.propTypes),
  submit: T.func.isRequired,
  denyCorrection: T.func.isRequired,
  showModal: T.func.isRequired,
  saveDocument: T.func.isRequired,
  submitForRevision: T.func.isRequired,
  saveRevisionComment: T.func.isRequired,
  saveDropComment: T.func.isRequired
}

export {
  MyDrop
}
