import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {url} from '#/main/app/api'
import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Button} from '#/main/app/action/components/button'

import {DropzoneType, DropType} from '#/plugin/drop-zone/resources/dropzone/prop-types'
import {selectors} from '#/plugin/drop-zone/resources/dropzone/store/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'
import {Documents} from '#/plugin/drop-zone/resources/dropzone/components/documents'
import {CorrectionCreation} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-creation'
import {CorrectionRow} from '#/plugin/drop-zone/resources/dropzone/correction/components/correction-row'

const Corrections = props =>
  <FormSections>
    <FormSection
      title={trans('corrections', {}, 'dropzone')}
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
    <div className="drop-nav">
      <Button
        className="btn-link btn-drop-nav"
        type={ASYNC_BUTTON}
        icon="fa fa-fw fa-chevron-left"
        label={trans('previous')}
        tooltip="right"
        request={{
          url: url(['claro_dropzone_drop_previous', {id: props.drop.id}])+props.slideshowQueryString,
          success: (previous) => {
            if (previous && previous.id) {
              props.history.push(`${props.path}/drop/${previous.id}`)
            }
          }
        }}
      />

      <div className="drop-content">
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
          documents={props.drop.documents}
          showUser={props.dropzone.parameters.dropType === constants.DROP_TYPE_TEAM}
          showTools={true}
          tools={props.tools}
          executeTool={props.executeTool}
        />
      </div>

      <Button
        className="btn-link btn-drop-nav"
        type={ASYNC_BUTTON}
        icon="fa fa-fw fa-chevron-right"
        label={trans('next')}
        tooltip="left"
        request={{
          url: url(['claro_dropzone_drop_next', {id: props.drop.id}])+props.slideshowQueryString,
          success: (next) => {
            if (next && next.id) {
              props.history.push(`${props.path}/drop/${next.id}`)
            }
          }
        }}
      />
    </div>

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
  path: T.string.isRequired,
  currentUser: T.object,
  dropzone: T.shape(DropzoneType.propTypes),
  drop: T.shape(DropType.propTypes),
  tools: T.array,
  saveCorrection: T.func.isRequired,
  executeTool: T.func.isRequired,
  slideshowQueryString: T.string,
  history: T.object.isRequired
}

const ConnectedDrop = withRouter(connect(
  (state) => ({
    currentUser: selectors.user(state),
    dropzone: selectors.dropzone(state),
    drop: selectors.currentDrop(state),
    tools: selectors.tools(state),
    slideshowQueryString: selectors.slideshowQueryString(state, selectors.STORE_NAME+'.drops')
  }),
  (dispatch) => ({
    saveCorrection: (correction) => dispatch(actions.saveCorrection(correction)),
    executeTool: (toolId, documentId) => dispatch(actions.executeTool(toolId, documentId))
  })
)(Drop))

export {ConnectedDrop as Drop}