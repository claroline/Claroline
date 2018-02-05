import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {Button} from '#/main/core/layout/button/components/button.jsx'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents.jsx'
import {MODAL_ADD_DOCUMENT} from '#/plugin/drop-zone/resources/dropzone/player/components/modal/add-document.jsx'

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
    {props.corrections.filter(c => c.finished).map((c, idx) =>
      <tr key={`correction-row-${c.id}`}>
        <td>
          {c.correctionDenied &&
          <span className="fa fa-fw fa-exclamation-triangle"/>
          }
        </td>
        <td>
          <a
            className="pointer-hand"
            onClick={() => {
              props.showModal('MODAL_CORRECTION', {
                title: trans('correction_n', {number: idx + 1}, 'dropzone'),
                correction: c,
                dropzone: props.dropzone,
                showDenialBox: props.dropzone.parameters.correctionDenialEnabled,
                denyCorrection: (correctionId, comment) => props.denyCorrection(correctionId, comment)
              })
            }}
          >
            {trans('correction_n', {number: idx + 1}, 'dropzone')}
          </a>
        </td>
        <td>{c.startDate}</td>
        <td>{c.endDate}</td>
        {props.dropzone.display.showScore &&
          <td>{c.score} / {props.dropzone.parameters.scoreMax}</td>
        }
      </tr>
    )}
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
    <h2 className="h-first">{trans('my_drop', {}, 'dropzone')}</h2>

    <HtmlText>{props.dropzone.instruction}</HtmlText>

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
        <Button
          className="btn-default"
          onClick={() => props.addDocument(props.myDrop.id, props.dropzone.parameters.documents)}
        >
          <span className="fa fa-fw fa-plus icon-with-text-right" />
          {trans('add_document', {}, 'dropzone')}
        </Button>

        <Button
          className="btn-primary"
          type="button"
          disabled={!props.myDrop.documents || 0 === props.myDrop.documents.length}
          onClick={() => props.submit(props.myDrop.id)}
        >
          <span className="fa fa-fw fa-upload icon-with-text-right" />
          {trans('submit_my_drop', {}, 'dropzone')}
        </Button>
      </div>
    }
  </section>

MyDropComponent.propTypes = {
  dropzone: T.shape(
    DropzoneType.propTypes
  ).isRequired,
  myDrop: T.shape(
    DropType.propTypes
  ).isRequired,
  isDropEnabled: T.bool.isRequired,
  submit: T.func.isRequired,
  denyCorrection: T.func.isRequired,
  showModal: T.func.isRequired,
  addDocument: T.func.isRequired,
  saveDocument: T.func.isRequired
}

const MyDrop = connect(
  (state) => ({
    dropzone: select.dropzone(state),
    myDrop: select.myDrop(state),
    isDropEnabled: select.isDropEnabled(state)
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
          title: trans('submit_my_drop', {}, 'dropzone'),
          question: trans('submit_my_drop_confirm', {}, 'dropzone'),
          confirmButtonText: trans('submit'),
          handleConfirm: () => dispatch(actions.submitDrop(id))
        })
      )
    },
    denyCorrection: (correctionId, comment) => dispatch(correctionActions.denyCorrection(correctionId, comment)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  })
)(MyDropComponent)

export {
  MyDrop
}
