import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isObject from 'lodash/isObject'
import get from 'lodash/get'

import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {tex, t} from '#/main/core/translation'
import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {ValidationStatus} from './validation-status.jsx'
import {RandomPicking} from './random-picking.jsx'
import {TagPicking} from './tag-picking.jsx'
import {AlertBlock} from '#/main/core/layout/alert/components/alert-block.jsx'
import {
  correctionModes,
  markModes,
  quizPicking,
  QUIZ_PICKING_DEFAULT,
  QUIZ_PICKING_TAGS,
  SHOW_CORRECTION_AT_DATE,
  TOTAL_SCORE_ON_CUSTOM,
  TOTAL_SCORE_ON_DEFAULT,
  NUMBERING_LITTERAL,
  NUMBERING_NONE,
  NUMBERING_NUMERIC,
  STATISTICS_ALL_PAPERS,
  statisticsModes
} from './../../enums'

import select from '#/plugin/exo/quiz/editor/selectors'

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
        id="quiz-description"
        label={tex('overview_message')}
        value={props.description}
        onChange={description => props.onChange('description', description)}
      />

      <CheckGroup
        id="quiz-show-metadata"
        value={props.parameters.showMetadata}
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
        id="quiz-end-message"
        label={tex('end_message')}
        value={props.parameters.endMessage}
        onChange={endMessage => props.onChange('parameters.endMessage', endMessage)}
      />

      <CheckGroup
        id="quiz-end-navigation"
        value={props.parameters.endNavigation}
        label={tex('show_end_navigation')}
        help={tex('show_end_navigation_help')}
        onChange={checked => props.onChange('parameters.endNavigation', checked)}
      />
    </ActivableSet>

    <RadiosGroup
      id="quiz-numbering"
      label={tex('quiz_numbering')}
      options={[
        {value: NUMBERING_NONE, label: tex('quiz_numbering_none')},
        {value: NUMBERING_NUMERIC, label: tex('quiz_numbering_numeric')},
        {value: NUMBERING_LITTERAL, label: tex('quiz_numbering_litteral')}
      ]}
      value={props.parameters.numbering}
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
    endNavigation: T.bool,
    numbering: T.string
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const Access = props =>
  <fieldset>
    <NumberGroup
      id="quiz-maxPapers"
      label={tex('maximum_papers')}
      min={0}
      value={props.parameters.maxPapers}
      onChange={maxPapers => props.onChange('parameters.maxPapers', maxPapers)}
      help={tex('maximum_papers_attempts_help')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.maxPapers')}
    />
  </fieldset>

Access.propTypes = {
  parameters: T.shape({
    maxPapers: T.number.isRequired
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

const Picking = props =>
  <fieldset>
    <SelectGroup
      id="quiz-picking"
      label={tex('quiz_picking_type')}
      choices={quizPicking}
      noEmpty={true}
      value={props.picking.type}
      onChange={(value) => props.onChange('picking.type', value)}
    />

    {QUIZ_PICKING_DEFAULT === props.picking.type &&
      <RandomPicking
        {...props.picking}
        onChange={(path, value) => props.onChange('picking.'+path, value)}
        validating={props.validating}
        errors={get(props, 'errors.picking')}
      />
    }

    {QUIZ_PICKING_TAGS === props.picking.type && 0 === props.tags.length &&
      <AlertBlock
        type={props.validating ? 'danger':'warning'}
        title={tex('quiz_no_tag')}
        message={tex('quiz_no_tag_help')}
      />
    }

    {QUIZ_PICKING_TAGS === props.picking.type &&
      <TagPicking
        {...props.picking}
        tags={props.tags}
        onChange={(path, value) => props.onChange('picking.'+path, value)}
        validating={props.validating}
        errors={get(props, 'errors.picking')}
      />
    }
  </fieldset>

Picking.propTypes = {
  tags: T.array.isRequired,
  picking: T.shape({
    type: T.string.isRequired
  }).isRequired,
  items: T.object.isRequired,
  validating: T.bool.isRequired,
  errors: T.object,
  onChange: T.func.isRequired
}

const Signing = props =>
  <fieldset>
    {/* TODO: enable this when feature is back
    <FormGroup
      id="quiz-duration"
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
    <NumberGroup
      id="quiz-maxAttempts"
      label={tex('maximum_attempts')}
      min={0}
      value={props.parameters.maxAttempts}
      onChange={maxAttempts => props.onChange('parameters.maxAttempts', maxAttempts)}
      help={tex('number_max_attempts_help')}
      warnOnly={!props.validating}
      error={get(props, 'errors.parameters.maxAttempts')}
    />

    {props.parameters.maxAttempts > 0 &&
      <NumberGroup
        id="quiz-maxAttemptsPerDay"
        label={tex('maximum_attempts_per_day')}
        min={0}
        value={props.parameters.maxAttemptsPerDay}
        onChange={maxAttemptsPerDay => props.onChange('parameters.maxAttemptsPerDay', maxAttemptsPerDay)}
        help={tex('number_max_attempts_per_day_help')}
        warnOnly={!props.validating}
        error={get(props, 'errors.parameters.maxAttemptsPerDay')}
      />
    }

    <CheckGroup
      id="quiz-interruptible"
      value={props.parameters.interruptible}
      label={tex('allow_test_exit')}
      onChange={checked => props.onChange('parameters.interruptible', checked)}
    />

    <CheckGroup
      id="quiz-mandatoryQuestions"
      value={props.parameters.mandatoryQuestions}
      label={tex('mandatory_questions')}
      onChange={checked => props.onChange('parameters.mandatoryQuestions', checked)}
    />

    <CheckGroup
      id="quiz-end-confirm"
      value={props.parameters.showEndConfirm}
      label={tex('show_end_confirm')}
      help={tex('show_end_confirm_help')}
      onChange={checked => props.onChange('parameters.showEndConfirm', checked)}
    />
</fieldset>

Signing.propTypes = {
  parameters: T.shape({
    duration: T.number.isRequired,
    maxAttempts: T.number.isRequired,
    mandatoryQuestions: T.bool.isRequired,
    maxAttemptsPerDay: T.number.isRequired,
    interruptible: T.bool.isRequired,
    showFeedback: T.bool.isRequired,
    showEndConfirm: T.bool
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
        <RadiosGroup
          id="quiz-total-score-on"
          label={tex('quiz_total_score_on')}
          options={[
            {value: TOTAL_SCORE_ON_DEFAULT, label: tex('quiz_total_score_on_mode_default')},
            {value: TOTAL_SCORE_ON_CUSTOM, label: tex('quiz_total_score_on_mode_custom')}
          ]}
          value={this.state.totalScoreOnMode}
          onChange={mode => this.handleScoreModeChange(mode)}
        />

        {this.state.totalScoreOnMode === TOTAL_SCORE_ON_CUSTOM &&
          <div className="sub-fields">
            <NumberGroup
              id="quiz-total-score-on-value"
              label={tex('quiz_total_score')}
              min={1}
              value={this.props.parameters.totalScoreOn || TOTAL_SCORE_ON_DEFAULT_VALUE}
              onChange={totalScore => this.props.onChange('parameters.totalScoreOn', totalScore)}
            />
          </div>
        }

        <NumberGroup
          id="quiz-success-score"
          label={tex('quiz_success_score')}
          min={0}
          max={100}
          unit="%"
          value={this.props.parameters.successScore}
          onChange={successScore => this.props.onChange('parameters.successScore', successScore)}
        />

        <FormGroup
          id="quiz-showCorrectionAt"
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
            <DateGroup
              id="quiz-correctionDate"
              label={tex('correction_date')}
              value={this.props.parameters.correctionDate}
              onChange={date => this.props.onChange('parameters.correctionDate', date)}
              time={true}
            />
          </div>
        }
        <FormGroup
          id="quiz-showScoreAt"
          label={tex('score_displaying')}
        >
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
          id="quiz-show-feedback"
          value={this.props.parameters.showFeedback}
          label={tex('show_feedback')}
          onChange={checked => this.props.onChange('parameters.showFeedback', checked)}
        />
        <CheckGroup
          id="quiz-anonymizeAttempts"
          value={this.props.parameters.anonymizeAttempts}
          label={tex('anonymous')}
          onChange={checked => this.props.onChange('parameters.anonymizeAttempts', checked)}
        />

        <CheckGroup
          id="quiz-showFullCorrection"
          value={this.props.parameters.showFullCorrection}
          label={tex('maximal_correction')}
          onChange={checked => this.props.onChange('parameters.showFullCorrection', checked)}
        />

        <ActivableSet
          id="quiz-showStatistics"
          label={tex('statistics')}
          activated={this.props.parameters.showStatistics}
          onChange={checked => this.props.onChange('parameters.showStatistics', checked)}
        >
          <FormGroup
            id="quiz-allPapersStatistics"
            label={tex('statistics_options')}
          >
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
        items={props.items}
        tags={props.tags}
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
  items: T.array.isRequired,
  tags: T.array.isRequired,
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

const QuizEditor = props =>
  <form>
    <PanelGroup
      accordion
      activeKey={props.activePanelKey}
    >
      {makePanel(Display, t('display_parameters'), 'display_mode', props)}
      {makePanel(Picking, tex('step_picking'), 'step-picking', props, ['picking'])}
      {makePanel(Signing, tex('signing'), 'signing', props, ['duration', 'maxAttempts'])}
      {makePanel(Correction, tex('correction'), 'correction', props)}
      {makePanel(Access, tex('access'), 'access', props)}
    </PanelGroup>
  </form>

QuizEditor.propTypes = {
  quiz: T.shape({
    description: T.string.isRequired,
    parameters: T.shape({
      type: T.string.isRequired,
      showOverview: T.bool.isRequired,
      showMetadata: T.bool.isRequired,
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
    }).isRequired,
    picking: T.object.isRequired
  }).isRequired,
  items: T.object.isRequired,
  tags: T.array.isRequired,
  validating: T.bool.isRequired,
  updateProperties: T.func.isRequired,
  activePanelKey: T.oneOfType([T.string, T.bool]).isRequired,
  handlePanelClick: T.func.isRequired
}

export {
  QuizEditor
}