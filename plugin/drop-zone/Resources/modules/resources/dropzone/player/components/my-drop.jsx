import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents.jsx'
import {DropForm} from '#/plugin/drop-zone/resources/dropzone/player/components/drop-form.jsx'

const Corrections = props =>
  <FormSections>
    <FormSection
      id="corrections-section"
      title={trans('corrections_list', {}, 'dropzone')}
    >
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
                  props.showModal(
                    'MODAL_CORRECTION',
                    {
                      title: trans('correction_n', {number: idx + 1}, 'dropzone'),
                      correction: c,
                      dropzone: props.dropzone,
                      showDenialBox: props.dropzone.parameters.correctionDenialEnabled,
                      denyCorrection: (correctionId, comment) => props.denyCorrection(correctionId, comment)
                    }
                  )
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
    </FormSection>
  </FormSections>

Corrections.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  corrections: T.array,
  denyCorrection: T.func,
  showModal: T.func
}

const MyDropComponent = props =>
  <div className="drop-panel">
    <h2>{trans('my_drop', {}, 'dropzone')}</h2>

    <FormSections>
      <FormSection
        id="instructions-section"
        title={trans('instructions', {}, 'dropzone')}
      >
        <HtmlText>
          {props.dropzone.instruction}
        </HtmlText>
      </FormSection>
    </FormSections>

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
      <DropForm
        allowedDocuments={props.dropzone.parameters.documents}
        saveDocument={props.saveDocument}
      />
    }

    {props.isDropEnabled && !props.myDrop.finished &&
      <button
        className="btn btn-primary pull-right"
        type="button"
        onClick={() => {
          props.showModal(MODAL_CONFIRM, {
            title: trans('warning', {}, 'dropzone'),
            question: trans('submit_my_copy_confirm_message', {}, 'dropzone'),
            handleConfirm: () => props.renderMyDrop()
          })
        }}
      >
        {trans('submit_my_copy', {}, 'dropzone')}
      </button>
    }
  </div>

MyDropComponent.propTypes = {
  dropzone: T.shape(DropzoneType.propTypes).isRequired,
  myDrop: T.shape(DropType.propTypes).isRequired,
  isDropEnabled: T.bool.isRequired,
  renderMyDrop: T.func.isRequired,
  denyCorrection: T.func.isRequired,
  showModal: T.func.isRequired,
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
    deleteDocument: (documentId) => dispatch(actions.deleteDocument(documentId)),
    renderMyDrop: () => dispatch(actions.renderMyDrop()),
    denyCorrection: (correctionId, comment) => dispatch(correctionActions.denyCorrection(correctionId, comment)),
    showModal: (type, props) => dispatch(modalActions.showModal(type, props))
  })
)(MyDropComponent)

export {
  MyDrop
}
