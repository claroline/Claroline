import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isObject from 'lodash/isObject'
import get from 'lodash/get'

import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {tex} from '#/main/core/translation'
import {formatDate} from '#/main/core/date'
import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {RadioGroup} from '#/main/core/layout/form/components/group/radio-group.jsx'
import {DatePicker} from '#/main/core/layout/form/components/field/date-picker.jsx'
import {ValidationStatus} from './validation-status.jsx'

import {
  shuffleModes,
  correctionModes,
  markModes,
  SHUFFLE_ALWAYS,
  SHUFFLE_ONCE,
  SHUFFLE_NEVER,
  SHOW_CORRECTION_AT_DATE,
  TOTAL_SCORE_ON_CUSTOM,
  TOTAL_SCORE_ON_DEFAULT,
  NUMBERING_LITTERAL,
  NUMBERING_NONE,
  NUMBERING_NUMERIC,
  STATISTICS_ALL_PAPERS,
  statisticsModes
} from './../../enums'

const TOTAL_SCORE_ON_DEFAULT_VALUE = 100

const Display = props =>
  <fieldset>
    <ActivableSet
      id="quiz-show-overview"
      label={tex('show_overview')}
      activated={props.parameters.showOverview}
      onChange={checked => props.onChange('parameters.showOverview', checked)}
    >
      <HtmlGroup
        controlId="quiz-description"
        label={tex('overview_message')}
        content={props.description}
        onChange={description => props.onChange('description', description)}
      />

      <CheckGroup
        checkId="quiz-show-metadata"
        checked={props.parameters.showMetadata}
        label={tex('metadata_visible')}
        help={tex('metadata_visible_help')}
        onChange={checked => props.onChange('parameters.showMetadata', checked)}
      />
    </ActivableSet>

    <ActivableSet
      id="quiz-show-end-page"
      label={tex('show_end_page')}
      activated={props.parameters.showEndPage}
      onChange={checked => props.onChange('parameters.showEndPage', checked)}
    >
      <HtmlGroup
        controlId="quiz-end-message"
        label={tex('end_message')}
        content={props.parameters.endMessage}
        onChange={endMessage => props.onChange('parameters.endMessage', endMessage)}
      />
    </ActivableSet>

    <RadioGroup
      controlId="quiz-numbering"
      label={tex('quiz_numbering')}
      options={[
        {value: NUMBERING_NONE, label: tex('quiz_numbering_none')},
        {value: NUMBERING_NUMERIC, label: tex('quiz_numbering_numeric')},
        {value: NUMBERING_LITTERAL, label: tex('quiz_numbering_litteral')}
      ]}
      checkedValue={props.parameters.numbering}
      onChange={numbering => props.onChange('parameters.numbering', numbering)}
    />
  </fieldset>

Display.propTypes = {
  description: T.string.isRequired,
  parameters: T.shape({
    type: T.string.isRequired,
    showOverview: T.bool.isRequired,
    showMetadata: T.bool.isRequired,
    showEndPage: T.bool.isRequired,
    endMessage: T.string,
    numbering: T.string
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const Access = props => {
  return (
    <fieldset>
      <FormGroup
        controlId="quiz-maxPapers"
        label={tex('maximum_papers')}
        help={tex('maximum_papers_attempts_help')}
        warnOnly={!props.validating}
        error={get(props, 'errors.parameters.maxPapers')}
      >
        <input
          id="quiz-maxPapers"
          type="number"
          min="0"
          value={props.parameters.maxPapers}
          className="form-control"
          onChange={e => props.onChange('parameters.maxPapers', e.target.value)}
        />
      </FormGroup>
    </fieldset>
  )
}

Access.propTypes = {
  parameters: T.shape({
    maxPapers: T.number.isRequired
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
    <RadioGroup
      controlId="quiz-random-pick"
      label={tex('random_picking')}
      options={shuffleOptions()}
      checkedValue={props.parameters.randomPick}
      onChange={mode => props.onChange('parameters.randomPick', mode)}
    />

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

    <RadioGroup
      controlId="quiz-random-order"
      label={tex('random_order')}
      options={orderModes(props.parameters.randomPick)}
      checkedValue={props.parameters.randomOrder}
      onChange={mode => props.onChange('parameters.randomOrder', mode)}
    />
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

    {props.parameters.maxAttempts > 0 &&
      <FormGroup
        controlId="quiz-maxAttemptsPerDay"
        label={tex('maximum_attempts_per_day')}
        help={tex('number_max_attempts_per_day_help')}
        warnOnly={!props.validating}
        error={get(props, 'errors.parameters.maxAttemptsPerDay')}
      >
        <input
          id="quiz-maxAttemptsPerDay"
          type="number"
          min="0"
          value={props.parameters.maxAttemptsPerDay}
          className="form-control"
          onChange={e => props.onChange('parameters.maxAttemptsPerDay', e.target.value)}
        />
      </FormGroup>
    }

    <CheckGroup
      checkId="quiz-interruptible"
      checked={props.parameters.interruptible}
      label={tex('allow_test_exit')}
      onChange={checked => props.onChange('parameters.interruptible', checked)}
    />

    <CheckGroup
      checkId="quiz-mandatoryQuestions"
      checked={props.parameters.mandatoryQuestions}
      label={tex('mandatory_questions')}
      onChange={checked => props.onChange('parameters.mandatoryQuestions', checked)}
    />
</fieldset>

Signing.propTypes = {
  parameters: T.shape({
    duration: T.number.isRequired,
    maxAttempts: T.number.isRequired,
    mandatoryQuestions: T.bool.isRequired,
    maxAttemptsPerDay: T.number.isRequired,
    interruptible: T.bool.isRequired,
    showFeedback: T.bool.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

class Correction extends Component {
  constructor(props) {
    super(props)
    this.state = {
      totalScoreOnMode: props.parameters.totalScoreOn && props.parameters.totalScoreOn > 0 ? TOTAL_SCORE_ON_CUSTOM : TOTAL_SCORE_ON_DEFAULT
    }
  }

  handleScoreModeChange(mode) {
    this.setState({totalScoreOnMode: mode})
    // reset value to default if needed
    if (mode ===  TOTAL_SCORE_ON_DEFAULT) {
      this.props.onChange('parameters.totalScoreOn', 0)
    }
  }

  render() {
    return(
      <fieldset>
        <RadioGroup
          controlId="quiz-total-score-on"
          label={tex('quiz_total_score_on')}
          options={[
            {value: TOTAL_SCORE_ON_DEFAULT, label: tex('quiz_total_score_on_mode_default')},
            {value: TOTAL_SCORE_ON_CUSTOM, label: tex('quiz_total_score_on_mode_custom')}
          ]}
          checkedValue={this.state.totalScoreOnMode}
          onChange={mode => this.handleScoreModeChange(mode)}
        />

        {this.state.totalScoreOnMode === TOTAL_SCORE_ON_CUSTOM &&
          <div className="sub-fields">
            <FormGroup
              controlId="quiz-total-score-on-value"
              label={tex('quiz_total_score')}
            >
              <input
                id="quiz-total-score-on-value"
                onChange={e => this.props.onChange('parameters.totalScoreOn', Number(e.target.value))}
                type="number"
                min="1"
                className="form-control"
                value={this.props.parameters.totalScoreOn || TOTAL_SCORE_ON_DEFAULT_VALUE}
              />
            </FormGroup>
          </div>
        }

        <FormGroup
          controlId="quiz-success-score"
          label={tex('quiz_success_score')}
        >
          <input
            id="quiz-success-score"
            onChange={e => this.props.onChange('parameters.successScore', e.target.value)}
            type="number"
            min="0"
            max="100"
            className="form-control"
            value={this.props.parameters.successScore || ''}
          />
        </FormGroup>

        <FormGroup
          controlId="quiz-showCorrectionAt"
          label={tex('availability_of_correction')}
        >
          <select
            id="quiz-showCorrectionAt"
            value={this.props.parameters.showCorrectionAt}
            className="form-control"
            onChange={e => this.props.onChange('parameters.showCorrectionAt', e.target.value)}
          >
            {correctionModes.map(mode =>
              <option key={mode[0]} value={mode[0]}>{tex(mode[1])}</option>
            )}
          </select>
        </FormGroup>
        {this.props.parameters.showCorrectionAt === SHOW_CORRECTION_AT_DATE &&
          <div className="sub-fields">
            <FormGroup
              controlId="quiz-correctionDate"
              label={tex('correction_date')}
            >
              <DatePicker
                id="quiz-correctionDate"
                name="quiz-correctionDate"
                value={this.props.parameters.correctionDate || ''}
                onChange={date => this.props.onChange('parameters.correctionDate', formatDate(date))}
              />
            </FormGroup>
          </div>
        }
        <FormGroup controlId="quiz-showScoreAt" label={tex('score_displaying')}>
          <select
            id="quiz-showScoreAt"
            value={this.props.parameters.showScoreAt}
            className="form-control"
            onChange={e => this.props.onChange('parameters.showScoreAt', e.target.value)}
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
          checked={this.props.parameters.showFeedback}
          label={tex('show_feedback')}
          onChange={checked => this.props.onChange('parameters.showFeedback', checked)}
        />
        <CheckGroup
          checkId="quiz-anonymizeAttempts"
          checked={this.props.parameters.anonymizeAttempts}
          label={tex('anonymous')}
          onChange={checked => this.props.onChange('parameters.anonymizeAttempts', checked)}
        />

        <CheckGroup
          checkId="quiz-showFullCorrection"
          checked={this.props.parameters.showFullCorrection}
          label={tex('maximal_correction')}
          onChange={checked => this.props.onChange('parameters.showFullCorrection', checked)}
        />

        <ActivableSet
          id="quiz-showStatistics"
          label={tex('statistics')}
          activated={this.props.parameters.showStatistics}
          onChange={checked => this.props.onChange('parameters.showStatistics', checked)}
        >
          <FormGroup controlId="quiz-allPapersStatistics" label={tex('statistics_options')}>
            <select
              id="quiz-allPapersStatistics"
              value={this.props.parameters.allPapersStatistics}
              className="form-control"
              onChange={e => this.props.onChange('parameters.allPapersStatistics', e.target.value === 'true')}
            >
              {statisticsModes.map(mode =>
                <option key={mode[0]} value={mode[0] === STATISTICS_ALL_PAPERS}>
                  {tex(mode[1])}
                </option>
              )}
            </select>
          </FormGroup>
        </ActivableSet>
      </fieldset>
    )
  }
}

Correction.propTypes = {
  parameters: T.shape({
    showCorrectionAt: T.string.isRequired,
    showScoreAt: T.string.isRequired,
    showFullCorrection: T.bool.isRequired,
    showStatistics: T.bool.isRequired,
    allPapersStatistics: T.bool.isRequired,
    showFeedback: T.bool.isRequired,
    anonymizeAttempts: T.bool.isRequired,
    correctionDate: T.string,
    totalScoreOn: T.number,
    successScore: T.number
  }).isRequired,
  onChange: T.func.isRequired
}

function makePanel(Section, title, key, props, errorProps) {
  const caretIcon = key === props.activePanelKey ?
    'fa-caret-down' :
    'fa-caret-right'

  const Header =
    <div onClick={() => props.handlePanelClick(key)} className="editor-panel-title">
      <span className={classes('fa fa-fw', caretIcon)}/>
      &nbsp;{title}
      {hasPanelError(props, errorProps) &&
        <ValidationStatus
          id={`quiz-${key}-status-tip`}
          validating={props.validating}
        />
      }
    </div>

  return (
    <Panel
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
        {makePanel(Display, tex('display_mode'), 'display_mode', props)}
        {makePanel(StepPicking, tex('step_picking'), 'step-picking', props, ['pick'])}
        {makePanel(Signing, tex('signing'), 'signing', props, ['duration', 'maxAttempts'])}
        {makePanel(Correction, tex('correction'), 'correction', props)}
        {makePanel(Access, tex('access'), 'access', props)}
      </PanelGroup>
    </form>
  )
}

QuizEditor.propTypes = {
  quiz: T.shape({
    description: T.string.isRequired,
    parameters: T.shape({
      type: T.string.isRequired,
      showOverview: T.bool.isRequired,
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
