import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isObject from 'lodash/isObject'
import get from 'lodash/get'

import Panel from 'react-bootstrap/lib/Panel'
import PanelGroup from 'react-bootstrap/lib/PanelGroup'

import {trans, tex} from '#/main/app/intl/translation'
import {Heading} from '#/main/core/layout/components/heading'
import {ActivableSet} from '#/main/core/layout/form/components/fieldset/activable-set.jsx'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group.jsx'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {RadiosGroup} from '#/main/core/layout/form/components/group/radios-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {AlertBlock} from '#/main/app/alert/components/alert-block'

import {
  correctionModes,
  markModes,
  quizPicking,
  QUIZ_PICKING_DEFAULT,
  QUIZ_PICKING_TAGS,
  SHOW_CORRECTION_AT_DATE,
  TOTAL_SCORE_ON_CUSTOM,
  TOTAL_SCORE_ON_DEFAULT
} from '#/plugin/exo/quiz/enums'
import select from '#/plugin/exo/quiz/editor/selectors'
import {ValidationStatus} from '#/plugin/exo/quiz/editor/components/validation-status.jsx'
import {TagPicking} from '#/plugin/exo/quiz/editor/components/tag-picking.jsx'

const TOTAL_SCORE_ON_DEFAULT_VALUE = 100

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

class Notation extends Component {
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
          choices={{
            [TOTAL_SCORE_ON_DEFAULT]: tex('quiz_total_score_on_mode_default'),
            [TOTAL_SCORE_ON_CUSTOM]: tex('quiz_total_score_on_mode_custom')
          }}
          value={this.state.totalScoreOnMode}
          onChange={mode => this.handleScoreModeChange(mode)}
        />

        {this.state.totalScoreOnMode === TOTAL_SCORE_ON_CUSTOM &&
        <div className="sub-fields">
          <NumberGroup
            id="quiz-total-score-on-value"
            label={trans('score_total')}
            min={1}
            value={this.props.parameters.totalScoreOn || TOTAL_SCORE_ON_DEFAULT_VALUE}
            onChange={totalScore => this.props.onChange('parameters.totalScoreOn', totalScore)}
          />
        </div>
        }

        <NumberGroup
          id="quiz-success-score"
          label={trans('score_to_pass')}
          min={0}
          max={100}
          unit="%"
          value={this.props.parameters.successScore}
          onChange={successScore => this.props.onChange('parameters.successScore', successScore)}
        />

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
      </fieldset>
    )
  }
}

Notation.propTypes = {
  parameters: T.shape({
    showScoreAt: T.string.isRequired,
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
  <div>
    <Heading level={2}>
      {trans('parameters')}
    </Heading>

    <PanelGroup
      accordion
      activeKey={props.activePanelKey}
    >
      {makePanel(Picking, tex('step_picking'), 'step-picking', props, ['picking'])}
      {makePanel(Correction, trans('correction'), 'correction', props)}
      {makePanel(Notation, trans('notation'), 'notation', props)}
    </PanelGroup>
  </div>

QuizEditor.propTypes = {
  quiz: T.shape({
    description: T.string.isRequired,
    parameters: T.shape({
      type: T.string.isRequired,
      showOverview: T.bool.isRequired,
      showMetadata: T.bool.isRequired,
      timeLimited: T.bool.isRequired,
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