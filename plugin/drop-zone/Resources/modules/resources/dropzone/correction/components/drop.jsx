import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents.jsx'
import {CorrectionCreation} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-creation.jsx'
import {CorrectionRow} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-row.jsx'

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
            <th>{trans('corrector', {}, 'dropzone')}</th>
            <th>{trans('start_date', {}, 'platform')}</th>
            <th>{trans('end_date', {}, 'platform')}</th>
            <th>{trans('score', {}, 'platform')}</th>
            <th>{trans('actions', {}, 'platform')}</th>
          </tr>
        </thead>
        <tbody>
        {props.corrections.map(c =>
          <CorrectionRow
            key={`correction-row-${c.id}`}
            correction={c}
          />
        )}
        </tbody>
      </table>
    </FormSection>
  </FormSections>

Corrections.propTypes = {
  corrections: T.array
}

const Drop = props => props.drop ?
  <div id="drop-container">
    <h2>
      {trans(
        'drop_from',
        {'name': props.dropzone.parameters.dropType === constants.DROP_TYPE_USER ?
          `${props.drop.user.firstName} ${props.drop.user.lastName}` :
          props.drop.teamName
        },
        'dropzone'
      )}
    </h2>
    <Documents
      documents={props.drop.documents || []}
      showUser={props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM}
      showTools={true}
      tools={props.tools}
      executeTool={props.executeTool}
    />

    {props.drop.corrections && props.drop.corrections.length > 0 &&
      <Corrections
        corrections={props.drop.corrections || []}
      />
    }
    {props.drop.finished &&
      <CorrectionCreation {...props}/>
    }
  </div> :
  <span className="fa fa-fw fa-circle-o-notch fa-spin"></span>


Drop.propTypes = {
  currentUser: T.object,
  dropzone: T.shape(DropzoneType.propTypes),
  drop: T.shape(DropType.propTypes),
  tools: T.array,
  saveCorrection: T.func.isRequired,
  executeTool: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    currentUser: select.user(state),
    dropzone: select.dropzone(state),
    drop: select.currentDrop(state),
    tools: select.tools(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    saveCorrection: (correction) => dispatch(actions.saveCorrection(correction)),
    executeTool: (toolId, documentId) => dispatch(actions.executeTool(toolId, documentId))
  }
}

const ConnectedDrop = connect(mapStateToProps, mapDispatchToProps)(Drop)

export {ConnectedDrop as Drop}