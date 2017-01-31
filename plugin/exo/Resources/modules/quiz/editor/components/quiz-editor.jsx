import React, {PropTypes as T} from 'react'
import isObject from 'lodash/isObject'
import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'
import get from 'lodash/get'
import classes from 'classnames'
import {t, tex} from './../../../utils/translate'
import {FormGroup} from './../../../components/form/form-group.jsx'
import {CheckGroup} from './../../../components/form/check-group.jsx'
import {Textarea} from './../../../components/form/textarea.jsx'
import {Radios} from './../../../components/form/radios.jsx'
import {Date} from './../../../components/form/date.jsx'
import {ValidationStatus} from './validation-status.jsx'
import {formatDate} from './../../../utils/date'

import {
  shuffleModes,
  correctionModes,
  markModes,
  SHUFFLE_ALWAYS,
  SHUFFLE_ONCE,
  SHUFFLE_NEVER,
  SHOW_CORRECTION_AT_DATE
} from './../../enums'

const Properties = props =>
  <fieldset>
    {/* TODO: enable this when feature is available
    <FormGroup controlId="quiz-type" label={t('type')}>
      <select
        id="quiz-type"
        value={props.parameters.type}
        className="form-control"
        onChange={e => props.onChange('parameters.type', e.target.value)}
      >
        {quizTypes.map(type =>
          <option key={type[0]} value={type[0]}>{tex(type[1])}</option>
        )}
      </select>
    </FormGroup>
    */}
    <FormGroup
      controlId="quiz-title"
      label={t('title')}
      warnOnly={!props.validating}
      error={get(props, 'errors.title')}
    >
      <input
        id="quiz-title"
        type="text"
        value={props.title}
        className="form-control"
        onChange={e => props.onChange('title', e.target.value)}
      />
    </FormGroup>
    <FormGroup controlId="quiz-description" label={t('description')}>
      <Textarea
        id="quiz-description"
        content={props.description}
        onChange={description => props.onChange('description', description)}
      />
    </FormGroup>
    <CheckGroup
      checkId="quiz-show-metadata"
      checked={props.parameters.showMetadata}
      label={tex('metadata_visible')}
      help={tex('metadata_visible_help')}
      onChange={checked => props.onChange('parameters.showMetadata', checked)}
    />
  </fieldset>

Properties.propTypes = {
  title: T.string.isRequired,
  description: T.string.isRequired,
  parameters: T.shape({
    type: T.string.isRequired,
    showMetadata: T.bool.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const shuffleOptions = () => {
  if (!shuffleOptions._options) {
    shuffleOptions._options = shuffleModes.map(mode => {
      return {
        value: mode[0],
        label: tex(mode[1])
      }
    })
  }

  return shuffleOptions._options
}

const orderModes = pickMode => {
  if (pickMode !== SHUFFLE_ALWAYS) {
    return shuffleOptions()
  }

  return shuffleOptions().filter(mode => mode.value !== SHUFFLE_ONCE)
}

const StepPicking = props =>
  <fieldset>
    <FormGroup controlId="quiz-random-pick" label={tex('random_picking')}>
      <Radios
        groupName="quiz-random-pick"
        options={shuffleOptions()}
        checkedValue={props.parameters.randomPick}
        onChange={mode => props.onChange('parameters.randomPick', mode)}
      />
    </FormGroup>
    {props.parameters.randomPick !== SHUFFLE_NEVER &&
      <div className="sub-fields">
        <FormGroup
          controlId="quiz-pick"
          label={tex('number_steps_draw')}
          help={tex('number_steps_draw_help')}
          warnOnly={!props.validating}
          error={get(props, 'errors.parameters.pick')}
        >
          <input
            id="quiz-pick"
            type="number"
            min="0"
            value={props.parameters.pick}
            className="form-control"
            onChange={e => props.onChange('parameters.pick', e.target.value)}
          />
        </FormGroup>
      </div>
    }
    <FormGroup controlId="quiz-random-order" label={tex('random_order')}>
      <Radios
        groupName="quiz-random-order"
        options={orderModes(props.parameters.randomPick)}
        checkedValue={props.parameters.randomOrder}
        onChange={mode => props.onChange('parameters.randomOrder', mode)}
      />
    </FormGroup>
  </fieldset>

StepPicking.propTypes = {
  parameters: T.shape({
    pick: T.number.isRequired,
    randomPick: T.string.isRequired,
    randomOrder: T.string.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const Signing = props =>
  <fieldset>
    {/* TODO: enable this when feature is back
    <FormGroup
      controlId="quiz-duration"
      label={tex('duration')}
      help={tex('duration_help')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.duration')}
    >
      <input
        id="quiz-duration"
        type="number"
        min="0"
        value={props.parameters.duration}
        className="form-control"
        onChange={e => props.onChange('parameters.duration', e.target.value)}
      />
    </FormGroup>
    */}
    <FormGroup
      controlId="quiz-maxAttempts"
      label={tex('maximum_attempts')}
      help={tex('number_max_attempts_help')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.maxAttempts')}
    >
      <input
        id="quiz-maxAttempts"
        type="number"
        min="0"
        value={props.parameters.maxAttempts}
        className="form-control"
        onChange={e => props.onChange('parameters.maxAttempts', e.target.value)}
      />
    </FormGroup>
    <CheckGroup
      checkId="quiz-interruptible"
      checked={props.parameters.interruptible}
      label={tex('allow_test_exit')}
      onChange={checked => props.onChange('parameters.interruptible', checked)}
    />
</fieldset>

Signing.propTypes = {
  parameters: T.shape({
    duration: T.number.isRequired,
    maxAttempts: T.number.isRequired,
    interruptible: T.bool.isRequired,
    showFeedback: T.bool.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const Correction = props =>
  <fieldset>
    <FormGroup
      controlId="quiz-showCorrectionAt"
      label={tex('availability_of_correction')}
    >
      <select
        id="quiz-showCorrectionAt"
        value={props.parameters.showCorrectionAt}
        className="form-control"
        onChange={e => props.onChange('parameters.showCorrectionAt', e.target.value)}
      >
        {correctionModes.map(mode =>
          <option key={mode[0]} value={mode[0]}>{tex(mode[1])}</option>
        )}
      </select>
    </FormGroup>
    {props.parameters.showCorrectionAt === SHOW_CORRECTION_AT_DATE &&
      <div className="sub-fields">
        <FormGroup
          controlId="quiz-correctionDate"
          label={tex('correction_date')}
        >
          <Date
            id="quiz-correctionDate"
            name="quiz-correctionDate"
            value={props.parameters.correctionDate || ''}
            onChange={date => props.onChange('parameters.correctionDate', formatDate(date))}
          />
        </FormGroup>
      </div>
    }
    <FormGroup controlId="quiz-showScoreAt" label={tex('score_displaying')}>
      <select
        id="quiz-showScoreAt"
        value={props.parameters.showScoreAt}
        className="form-control"
        onChange={e => props.onChange('parameters.showScoreAt', e.target.value)}
      >
        {markModes.map(mode =>
          <option key={mode[0]} value={mode[0]}>
            {tex(mode[1])}
          </option>
        )}
      </select>
    </FormGroup>
    <CheckGroup
      checkId="quiz-show-feedback"
      checked={props.parameters.showFeedback}
      label={tex('show_feedback')}
      onChange={checked => props.onChange('parameters.showFeedback', checked)}
    />
    <CheckGroup
      checkId="quiz-anonymizeAttempts"
      checked={props.parameters.anonymizeAttempts}
      label={tex('anonymous')}
      onChange={checked => props.onChange('parameters.anonymizeAttempts', checked)}
    />
    <CheckGroup
      checkId="quiz-showFullCorrection"
      checked={props.parameters.showFullCorrection}
      label={tex('maximal_correction')}
      onChange={checked => props.onChange('parameters.showFullCorrection', checked)}
    />
    <CheckGroup
      checkId="quiz-showStatistics"
      checked={props.parameters.showStatistics}
      label={tex('statistics')}
      onChange={checked => props.onChange('parameters.showStatistics', checked)}
    />
  </fieldset>

Correction.propTypes = {
  parameters: T.shape({
    showCorrectionAt: T.string.isRequired,
    showScoreAt: T.string.isRequired,
    showFullCorrection: T.bool.isRequired,
    showStatistics: T.bool.isRequired,
    showFeedback: T.bool.isRequired,
    anonymizeAttempts: T.bool.isRequired,
    correctionDate: T.string
  }).isRequired,
  onChange: T.func.isRequired
}

function makePanel(Section, title, key, props, errorProps) {
  const caretIcon = key === props.activePanelKey ?
    'fa-caret-down' :
    'fa-caret-right'

  const Header =
    <div onClick={() => props.handlePanelClick(key)}>
      <span className="panel-title">
        <span className={classes('panel-icon', 'fa', caretIcon)}/>
        &nbsp;{title}
      </span>
      {hasPanelError(props, errorProps) &&
        <ValidationStatus
          id={`quiz-${key}-status-tip`}
          validating={props.validating}
        />
      }
    </div>

  return (
    <Panel
      className="editor-panel-title"
      eventKey={key}
      header={Header}
    >
      <Section
        onChange={props.updateProperties}
        errors={props.quiz._errors}
        validating={props.validating}
        {...props.quiz}
      />
    </Panel>
  )
}

makePanel.propTypes = {
  activePanelKey: T.string.isRequired,
  validating: T.bool.isRequired,
  handlePanelClick: T.func.isRequired,
  updateProperties: T.func.isRequired,
  quiz: T.object.isRequired,
  _errors: T.object
}

function hasPanelError(allProps, errorPropNames) {
  if (!errorPropNames || !isObject(allProps.quiz._errors)) {
    return false
  }

  const errorFields = Object.keys(allProps.quiz._errors)

  return !!errorPropNames.find(name =>
    !!errorFields.find(field => field === name)
  )
}

export const QuizEditor = props => {
  return (
    <form>
      <PanelGroup
        accordion
        activeKey={props.activePanelKey}
      >
        {makePanel(Properties, t('properties'), 'properties', props, ['title'])}
        {makePanel(StepPicking, tex('step_picking'), 'step-picking', props, ['pick'])}
        {makePanel(Signing, tex('signing'), 'signing', props, ['duration', 'maxAttempts'])}
        {makePanel(Correction, tex('correction'), 'correction', props)}
      </PanelGroup>
    </form>
  )
}

QuizEditor.propTypes = {
  quiz: T.shape({
    title: T.string.isRequired,
    description: T.string.isRequired,
    parameters: T.shape({
      type: T.string.isRequired,
      showMetadata: T.bool.isRequired,
      randomOrder: T.string.isRequired,
      randomPick: T.string.isRequired,
      pick: T.number.isRequired,
      duration: T.number.isRequired,
      maxAttempts: T.number.isRequired,
      interruptible: T.bool.isRequired,
      showCorrectionAt: T.string.isRequired,
      correctionDate: T.string,
      anonymizeAttempts: T.bool.isRequired,
      showScoreAt: T.string.isRequired,
      showStatistics: T.bool.isRequired,
      showFullCorrection: T.bool.isRequired,
      showFeedback: T.bool.isRequired
    }).isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  updateProperties: T.func.isRequired,
  activePanelKey: T.oneOfType([T.string, T.bool]).isRequired,
  handlePanelClick: T.func.isRequired
}
