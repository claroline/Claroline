import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {ContentComments} from '#/main/app/content/components/comments'
import {ContentTitle} from '#/main/app/content/components/title'
import {MODAL_SELECTION} from '#/main/app/modals/selection'
import {MODAL_ALERT} from '#/main/app/modals/alert'

import {DropzoneType, DropType, Revision as RevisionType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/modals/document'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {Corrections} from '#/plugin/drop-zone/resources/dropzone/components/corrections'

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
    />

    {props.dropzone.display.displayCorrectionsToLearners && props.myDrop.finished && props.myDrop.corrections.filter(c => c.finished).length > 0 &&
      <Corrections
        dropzone={props.dropzone}
        corrections={props.myDrop.corrections.filter(c => c.finished && c.valid)}
        denyCorrection={props.denyCorrection}
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
            callback: () => props.submitForRevision(props.myDrop.id),
            confirm: {
              message: trans('submit_for_revision_confirm', {}, 'dropzone')
            }
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
            icon: 'fa fa-fw fa-check-double',
            label: trans('submit_my_drop', {}, 'dropzone'),
            callback: () => props.submit(props.myDrop.id),
            disabled: !props.myDrop.documents || 0 === props.myDrop.documents.length,
            primary: true,
            confirm: {
              title: trans('final_drop', {}, 'dropzone'),
              message: trans('submit_my_drop_confirm', {}, 'dropzone')
            }
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
  saveDocument: T.func.isRequired,
  submitForRevision: T.func.isRequired,
  saveRevisionComment: T.func.isRequired,
  saveDropComment: T.func.isRequired
}

export {
  MyDrop
}
