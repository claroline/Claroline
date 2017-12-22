import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {generateUrl} from '#/main/core/fos-js-router'
import {t, tex} from '#/main/core/translation'
import {
  Page,
  PageHeader,
  PageContent,
  PageActions,
  PageAction
} from '#/main/core/layout/page/index'

import {BarChart} from '#/main/core/layout/chart/bar/components/bar-chart.jsx'
import {PieChart} from '#/main/core/layout/chart/pie/components/pie-chart.jsx'
import {CircularGauge} from '#/main/core/layout/chart/gauge/components/circlular-gauge.jsx'

import {select} from './../selectors'

const COLOR_SUCCESS = '#4F7302'
const COLOR_WARNING = '#F0AD4E'
const COLOR_DANGER  = '#BF0404'

const CountCard = props =>
  <div className="count-card panel panel-default">
    <span className={`icon ${props.icon}`} />
    <div className="panel-body text-right">
      {props.label}
      <div className="h3 text-right text-info">{props.count}</div>
    </div>
  </div>

CountCard.propTypes = {
  icon: T.string.isRequired,
  label: T.string.isRequired,
  count: T.number.isRequired
}

const GeneralStats = props =>
  <div className="general-stats">
    <CountCard label={tex('steps')} icon="fa fa-th-list" count={props.statistics.nbSteps} />
    <CountCard label={tex('questions')} icon="fa fa-question" count={props.statistics.nbQuestions} />
    <CountCard label={t('users')} icon="fa fa-user" count={props.statistics.nbRegisteredUsers} />
    <CountCard label={t('anonymous')} icon="fa fa-user-secret" count={props.statistics.nbAnonymousUsers} />
    <CountCard label={tex('papers')} icon="fa fa-file" count={props.statistics.nbPapers} />
  </div>

GeneralStats.propTypes = {
  statistics: T.shape({
    nbSteps: T.number.isRequired,
    nbQuestions: T.number.isRequired,
    nbRegisteredUsers: T.number.isRequired,
    nbAnonymousUsers: T.number.isRequired,
    nbPapers: T.number.isRequired
  }).isRequired
}

const SuccessDistribution = props =>
  <div className="paper-success-distribution">
    <h2>{tex('docimology_success_index')}</h2>

    <div className="row">
      <div className="col-md-4" style={{marginBottom: '20px'}}>
        <PieChart
          data={[
            props.nbSuccess,
            props.nbPartialSuccess,
            props.nbMissed
          ]}
          colors={[COLOR_SUCCESS, COLOR_WARNING, COLOR_DANGER]}
          width={280}
          showValue={true}
        />
      </div>

      <div className="col-md-8" style={{marginBottom: '20px'}}>
        <ul className="legend">
          <li className="inline-flex">
            <span className="color-legend" style={{backgroundColor: COLOR_SUCCESS}} />
            <span className="legend-label">
              {tex('docimology_papers_totally_successfull')} {`(${props.nbSuccess})`}
            </span>
          </li>
          <li className="inline-flex">
            <span className="color-legend" style={{backgroundColor: COLOR_WARNING}} />
            <span className="legend-label">
              {tex('docimology_papers_partially_successfull')} {`(${props.nbPartialSuccess})`}
            </span>
          </li>
          <li className="inline-flex">
            <span className="color-legend" style={{backgroundColor: COLOR_DANGER}} />
            <span className="legend-label">
              {tex('docimology_papers_missed')} {`(${props.nbMissed})`}
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>

SuccessDistribution.propTypes = {
  nbSuccess: T.number.isRequired,
  nbPartialSuccess: T.number.isRequired,
  nbMissed: T.number.isRequired
}

const ScoreDistribution = props =>
  <div className="paper-score-distribution">
    <h2>{tex('docimology_score_distribution')}</h2>
    <div className="help-block">
      <span className="fa fa-fw fa-info-circle" />
      {tex('docimology_note_gauges_help')}
    </div>

    <div className="row">
      <div className="col-md-6">
        <div className="panel panel-default">
          <div className="panel-body">
            <BarChart
              data={props.paperScoreDistribution}
              width={540}
              height={250}
              margin={{top: 20, right: 20, bottom: 80, left: 80}}
              yAxisLabel={{
                show: true,
                text: tex('docimology_nb_papers')
              }}
              xAxisLabel={{
                show: true,
                text: tex('docimology_score')
              }}
            />
          </div>
        </div>
      </div>
      <div className="note-gauges col-md-6">
        <CircularGauge
          label={tex('minimum')}
          color={COLOR_DANGER}
          value={props.minMaxAndAvgScores.min}
          max={props.maxScore}
          width={180}
          size={25}
          showValue={false}
        />
        <CircularGauge
          label={tex('average')}
          color={COLOR_WARNING}
          value={props.minMaxAndAvgScores.avg}
          max={props.maxScore}
          width={180}
          size={25}
          showValue={false}
        />
        <CircularGauge
          label={tex('maximum')}
          color={COLOR_SUCCESS}
          value={props.minMaxAndAvgScores.max}
          max={props.maxScore}
          width={180}
          size={25}
          showValue={false}
        />
      </div>
    </div>
  </div>

ScoreDistribution.propTypes = {
  paperScoreDistribution : T.object.isRequired,
  maxScore: T.number.isRequired,
  minMaxAndAvgScores: T.shape({
    min: T.number.isRequired,
    max: T.number.isRequired,
    avg: T.number.isRequired
  }).isRequired
}

const DifficultyIndex = props =>
  <div className="difficulty-index">
    <h2>{tex('docimology_difficulty_index')}</h2>

    <div className="panel panel-default">
      <div className="panel-body">
        <BarChart
          data={props.questionsDifficultyIndex}
          width={720}
          height={350}
          margin={{top: 20, right: 20, bottom: 80, left: 80}}
          yAxisLabel={{
            show: true,
            text: tex('docimology_difficulty_index')
          }}
          xAxisLabel={{
            show: true,
            text: tex('docimology_question_title')
          }}
        />

        <div className="docimology-help">
          <div className="icon">
            <span className="help-block">
              <span className="fa fa-fw fa-info-circle" />
            </span>
          </div>
          <div className="text">
            <span className="help-block">{tex('docimology_difficulty_index_help_part_1')}</span>
            <span className="help-block">{tex('docimology_difficulty_index_help_part_2')}</span>
            <span className="help-block">{tex('docimology_difficulty_index_help_part_3')}</span>
            <span className="help-block">{tex('docimology_difficulty_index_help_part_4')}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

DifficultyIndex.propTypes = {
  questionsDifficultyIndex: T.object.isRequired
}

const DiscriminationIndex = props =>
  <div className="discrimination-index">
    <h2>{tex('docimology_discrimination_coefficient')}</h2>

    <div className="panel panel-default">
      <div className="panel-body">
        <BarChart
          data={props.discriminationCoefficient}
          width={720}
          height={350}
          margin={{top: 20, right: 20, bottom: 80, left: 80}}
          yAxisLabel={{
            show: true,
            text: tex('docimology_discrimination_coefficient')
          }}
          xAxisLabel={{
            show: true,
            text: tex('docimology_question_title')
          }}
          minMaxAsYDomain={true}
          ticksAsYValues={true}
        />

        <div className="docimology-help">
          <div className="icon">
            <span className="help-block">
              <span className="fa fa-fw fa-info-circle" />
            </span>
          </div>
          <div className="text">
            <span className="help-block">{tex('docimology_discrimination_coefficient_help_part_1')}</span>
            <span className="help-block">{tex('docimology_discrimination_coefficient_help_part_2')}</span>
            <span className="help-block">{tex('docimology_discrimination_coefficient_help_part_3')}</span>
            <span className="help-block">{tex('docimology_discrimination_coefficient_help_part_4')}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

DiscriminationIndex.propTypes = {
  discriminationCoefficient: T.object.isRequired
}

const Docimology = props =>
  <Page id="quiz-docimology">
    <PageHeader
      title={props.quiz.title}
      subtitle={tex('docimology')}
    >
      <PageActions>
        <PageAction
          id="back-to-exercise"
          title={tex('back_to_the_quiz')}
          icon="fa fa-fw fa-sign-out"
          action={generateUrl('ujm_exercise_open', {id: props.quiz.id})}
        />
      </PageActions>
    </PageHeader>

    <PageContent>
      <GeneralStats
        {...props}
      />

      <SuccessDistribution
        {...props.statistics.paperSuccessDistribution}
      />

      <ScoreDistribution
        maxScore={props.statistics.maxScore}
        paperScoreDistribution={props.statistics.paperScoreDistribution}
        minMaxAndAvgScores={props.statistics.minMaxAndAvgScores}
      />

      <DifficultyIndex
        questionsDifficultyIndex={props.statistics.questionsDifficultyIndex}
      />

      <DiscriminationIndex
        discriminationCoefficient={props.statistics.discriminationCoefficient}
      />
    </PageContent>
  </Page>

Docimology.propTypes = {
  quiz: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }).isRequired,
  statistics: T.shape({
    maxScore: T.number.isRequired,
    minMaxAndAvgScores: T.object.isRequired,
    paperScoreDistribution: T.object.isRequired,
    paperSuccessDistribution: T.object.isRequired,
    questionsDifficultyIndex: T.object.isRequired,
    discriminationCoefficient: T.object.isRequired
  }).isRequired
}

function mapStateToProps(state) {
  return {
    quiz: select.quiz(state),
    statistics: select.statistics(state)
  }
}

const ConnectedDocimology = connect(mapStateToProps, null)(Docimology)

export {
  ConnectedDocimology as Docimology
}
