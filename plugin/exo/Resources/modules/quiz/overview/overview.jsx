import React, {Component, PropTypes as T} from 'react'
import {connect} from 'react-redux'
import classes from 'classnames'
import {tex} from './../../utils/translate'
import select from './../selectors'
import {
  correctionModes,
  markModes,
  quizTypes,
  SHOW_CORRECTION_AT_DATE
} from './../enums'
import {actions as playerActions} from './../player/actions'

const Parameter = props =>
  <tr>
    <th className="text-right col-md-4" scope="row">
      {tex(props.name)}
    </th>
    <td className="text-center col-md-8">
      {props.children}
    </td>
  </tr>

Parameter.propTypes = {
  name: T.string.isRequired,
  children: T.any.isRequired
}

const Parameters = props =>
  <div className="panel panel-default">
    <table className="table table-striped table-bordered">
      <tbody>
        {props.parameters.duration > 0 &&
          <Parameter name="duration">{props.parameters.duration}</Parameter>
        }
        <Parameter name="availability_of_correction">
          {props.parameters.showCorrectionAt === SHOW_CORRECTION_AT_DATE ?
            props.parameters.correctionDate :
            tex(correctionModes.find(mode => mode[0] === props.parameters.showCorrectionAt)[1])
          }
        </Parameter>
        <Parameter name="availability_of_score">
          {tex(markModes.find(mode => mode[0] === props.parameters.showScoreAt)[1])}
        </Parameter>
      </tbody>
        {props.editable && props.additionalInfo &&
          <tbody>
            <Parameter name="type">
              {tex(quizTypes.find(type => type[0] === props.parameters.type)[1])}
            </Parameter>
            <Parameter name="creation_date">{props.meta.created}</Parameter>
            <Parameter name="number_steps_draw">
              {props.parameters.pick || tex('all_step')}
            </Parameter>
            <Parameter name="random_steps">
              {tex(props.parameters.randomOrder ? 'yes' : 'no')}
            </Parameter>
            <Parameter name="keep_same_step">
              {tex(props.parameters.randomPick ? 'no' : 'yes')}
            </Parameter>
            <Parameter name="anonymous">
              {tex(props.parameters.anonymizeAttempts ? 'yes' : 'no')}
            </Parameter>
            <Parameter name="test_exit">
              {tex(props.parameters.interruptible ? 'yes' : 'no')}
            </Parameter>
            <Parameter name="maximum_tries">
              {props.parameters.maxAttempts || '-'}
            </Parameter>
          </tbody>
        }
    </table>
    {props.editable &&
      <div
        className="panel-footer text-center toggle-exercise-info"
        role="button"
        onClick={props.onAdditionalToggle}
      >
        <span className={classes('fa', 'fa-fw', props.additionalInfo ? 'fa-caret-up' : 'fa-caret-right')}/>
        {tex(props.additionalInfo ? 'hide_additional_info' : 'show_additional_info')}
      </div>
    }
  </div>

Parameters.propTypes = {
  editable: T.bool.isRequired,
  additionalInfo: T.bool.isRequired,
  onAdditionalToggle: T.func.isRequired,
  parameters: T.shape({
    type: T.string.isRequired,
    randomOrder: T.string.isRequired,
    randomPick: T.string.isRequired,
    pick: T.number.isRequired,
    duration: T.number.isRequired,
    maxAttempts: T.number.isRequired,
    interruptible: T.bool.isRequired,
    showCorrectionAt: T.string.isRequired,
    correctionDate: T.string,
    anonymizeAttempts: T.bool.isRequired,
    showScoreAt: T.string.isRequired
  }).isRequired,
  meta: T.shape({
    created: T.string.isRequired
  })
}

const Layout = props =>
  <div className="quiz-overview">
    {props.empty &&
      <div className="alert alert-info text-center">
        <span className="fa fa-fw fa-warning"></span>
        <span>
          {tex(props.editable ?
            'exo_empty_user_can_edit' :
            'exo_empty_user_read_only'
          )}
        </span>
      </div>
    }

    {props.description &&
      <div className="exercise-description panel panel-default">
        <div
          className="panel-body"
          dangerouslySetInnerHTML={{ __html: props.description }}
        ></div>
      </div>
    }
    {props.parameters.showMetadata &&
      <Parameters {...props}/>
    }

    {props.empty && props.editable &&
      <a href="#/steps" role="button" className="btn btn-block btn-primary btn-lg">
        <span className="fa fa-pencil"></span>
        {tex('edit')}
      </a>
    }
    {!props.empty &&
      (props.parameters.maxAttempts === 0
        || props.meta.userPaperCount < props.parameters.maxAttempts) &&
      <a href="#play" className="btn btn-start btn-lg btn-primary btn-block">
        <span className="fa fa-fw fa-play"></span>
        {tex('exercise_start')}
      </a>
    }

  </div>

Layout.propTypes = {
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  description: T.string,
  onAdditionalToggle: T.func.isRequired,
  parameters: T.shape({
    showMetadata: T.bool.isRequired,
    maxAttempts: T.number.isRequired
  }).isRequired,
  meta: T.shape({
    created: T.string.isRequired,
    userPaperCount: T.number.isRequired
  })
}

Layout.defaultProps = {
  description: null
}

class Overview extends Component {
  constructor(props) {
    super(props)
    this.state = {
      additionalInfo: false
    }
  }

  render() {
    return (
      <Layout
        empty={this.props.empty}
        editable={this.props.editable}
        description={this.props.quiz.description}
        parameters={this.props.quiz.parameters}
        meta={this.props.quiz.meta}
        play={() => this.props.play(this.props.quiz, this.props.steps)}
        additionalInfo={this.state.additionalInfo}
        onAdditionalToggle={() => this.setState({
          additionalInfo: !this.state.additionalInfo
        })}
      />
    )
  }
}

Overview.propTypes = {
  empty: T.bool.isRequired,
  editable: T.bool.isRequired,
  quiz: T.shape({
    description: T.string,
    parameters: T.shape({
      showMetadata: T.bool.isRequired,
      type: T.string.isRequired,
      randomOrder: T.string.isRequired,
      randomPick: T.string.isRequired,
      pick: T.number.isRequired,
      duration: T.number.isRequired,
      maxAttempts: T.number.isRequired,
      interruptible: T.bool.isRequired,
      showCorrectionAt: T.string.isRequired,
      correctionDate: T.string,
      anonymizeAttempts: T.bool.isRequired,
      showScoreAt: T.string.isRequired
    }).isRequired,
    meta: T.shape({
      created: T.string.isRequired,
      userPaperCount: T.number.isRequired
    }).isRequired
  }).isRequired,
  steps: T.object.isRequired,
  play: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    empty: select.empty(state),
    editable: select.editable(state),
    quiz: select.quiz(state),
    steps: select.steps(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    play() {
      // TODO : optimisation - we may want to get a local paper if exists to avoid calling the server
      dispatch(playerActions.play())
    }
  }
}

const ConnectedOverview = connect(mapStateToProps, mapDispatchToProps)(Overview)

export {ConnectedOverview as Overview}
