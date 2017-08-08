import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {withRouter} from 'react-router-dom'
import classes from 'classnames'
import {trans} from '#/main/core/translation'
import {actions} from '../actions'

class CompetencyView extends Component {
  componentDidMount() {
    this.props.fetchCompetencyData(this.props.match.params.oId, this.props.match.params.cId)
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.match.params.oId !== nextProps.match.params.oId || this.props.match.params.cId !== nextProps.match.params.cId) {
      this.props.fetchCompetencyData(nextProps.match.params.oId, nextProps.match.params.cId)
    }
  }

  componentWillUnmount() {
    this.props.resetCompetency()
  }

  getSuccessRate() {
    return this.props.challenge.nbPassed > 0 && this.props.challenge.nbToPass > 0 ?
      Math.round((this.props.challenge.nbPassed / this.props.challenge.nbToPass) * 100) :
      0
  }

  isCompetencyAcquired(competency) {
    return competency.userLevelValue !== undefined && competency.userLevelValue >= competency.requiredLevel
  }

  isRequiredLevel(competency, level) {
    return level <= competency.requiredLevel
  }

  isAquiredLevel(competency, level) {
    return competency.userLevelValue !== undefined && level <= competency.userLevelValue
  }

  render() {
    return (
      <div>
        {this.props.competency &&
          <div>
            <div className="my-objectives-tool-title">
              {this.props.objective.name}
            </div>
            <div className="my-objectives-tool-content">
              <div className="panel panel-default competency-panel">
                <div className="panel-heading">
                  <a
                    role="button"
                    data-toggle="collapse"
                    href="#competency-title-extra"
                    aria-expanded="true"
                    aria-controls="competency-title-extra"
                  >
                    {this.props.competency.name}
                    &nbsp;
                    &nbsp;
                    <i className="fa fa-caret-square-o-down"></i>
                  </a>
                </div>
                <div
                  id="competency-title-extra"
                  className="competency-extra-box panel-collapse collapse"
                  role="tabpanel"
                >
                  <div className="competency-extra-content">
                    {trans('objective.select_competency', {}, 'competency')} :
                    <ul>
                      {this.props.competencies && Object.keys(this.props.competencies).map(competencyId =>
                        <li key={competencyId}>
                          <a href={`#/${this.props.objective.id}/competency/${competencyId}`}>
                            {this.props.competency.id === parseInt(competencyId) ?
                              <b>
                                {this.props.competencies[competencyId].name}
                              </b> :
                              this.props.competencies[competencyId].name
                            }
                          </a>
                          <span className="extra-icons">
                            {this.isCompetencyAcquired(this.props.competencies[competencyId]) ?
                              <i className="fa fa-check-square-o competency-color-success"></i> :
                              <span className="competency-color-info">
                                {[...Array(this.props.competencies[competencyId].requiredLevel + 1)].map((x, index) =>
                                  <i
                                    key={index}
                                    className={`fa fa-star${this.isAquiredLevel(this.props.competencies[competencyId], index) ? '' : '-o'}`}
                                  >
                                  </i>
                                )}
                              </span>
                            }
                          </span>
                        </li>
                      )}
                    </ul>
                  </div>
                </div>
                <div className="panel-body">
                  {this.props.challenge.error &&
                    <div className="alert alert-danger challenge-error-alert">
                      {this.props.challenge.error}
                    </div>
                  }
                  <div className="level">
                    {this.props.competencies && [...Array(this.props.competencies[this.props.competency.id].nbLevels)].map((x, index) =>
                      <span
                          key={index}
                          className={classes(
                            'level-icon',
                            {
                              'current-level-icon': this.props.currentLevel === index,
                              'extra-level': !this.isRequiredLevel(this.props.competencies[this.props.competency.id], index)
                            }
                          )}
                      >
                        <i
                          className={`
                            fa fa-2x pointer-hand
                            fa-star${this.isAquiredLevel(this.props.competencies[this.props.competency.id], index) ? '' : '-o'}
                          `}
                          aria-hidden="true"
                          onClick={() => this.props.getLevelData(this.props.competency.id, index)}
                        >
                        </i>
                      </span>
                    )}
                  </div>
                  <div className="current-level">
                    <div className="change-level">
                      {this.props.currentLevel > 0 &&
                        <a
                          className="pointer-hand change-level-link"
                          onClick={() => this.props.getLevelData(this.props.competency.id, this.props.currentLevel - 1)}
                        >
                          <i className="fa fa-arrow-circle-left fa-5x" aria-hidden="true"></i>
                          {trans('objective.previous_level', {}, 'competency')}
                        </a>
                      }
                    </div>
                    <div className="current-level-content">
                      <div className="current-level-challenge">
                        <h3>{trans('objective.your_challenge', {}, 'competency')} :</h3>
                        {trans('objective.pass_n_resources', {n: this.props.challenge.nbToPass}, 'competency')}
                      </div>
                      {(!this.props.progress.level || (this.props.progress.level && this.props.progress.level.value < this.props.currentLevel)) &&
                        <div className={`c100 p${this.getSuccessRate()} small`}>
                          <span>{this.getSuccessRate()}%</span>
                          <div className="slice">
                            <div className="bar"></div>
                            <div className="fill"></div>
                          </div>
                        </div>
                      }
                      {this.props.progress.level && this.props.progress.level.value >= this.props.currentLevel &&
                        <div className="acquired-content">
                          <i className="fa fa-check-square-o fa-2x" aria-hidden="true"></i>
                          {trans('competency.acquired', {}, 'competency')}
                        </div>
                      }
                    </div>
                    <div className="change-level">
                      {this.props.currentLevel + 1 < this.props.nbLevels &&
                        <a
                          className="pointer-hand change-level-link"
                          onClick={() => this.props.getLevelData(this.props.competency.id, this.props.currentLevel + 1)}
                        >
                          {trans('objective.next_level', {}, 'competency')}
                          <i className="fa fa-arrow-circle-right fa-5x" aria-hidden="true"></i>
                        </a>
                      }
                    </div>
                  </div>
                  {!this.props.challenge.error &&
                    <button
                      className="btn btn-primary"
                      onClick={() => this.props.getRelevantResource(this.props.competency.id, this.props.currentLevel)}
                    >
                      {(this.props.progress.level && this.props.progress.level.value >= this.props.currentLevel) || this.getSuccessRate() === 100 ?
                        trans('objective.train', {}, 'competency') :
                        this.props.challenge.nbPassed === 0 ?
                          trans('objective.start', {}, 'competency') :
                          trans('objective.continue', {}, 'competency')
                      }
                    </button>
                  }
                </div>
              </div>
            </div>
          </div>
        }
      </div>
    )
  }
}

CompetencyView.propTypes = {
  match: T.shape({
    path: T.string.isRequired,
    url: T.string.isRequired,
    params: T.shape({
      oId: T.string.isRequired,
      cId: T.string.isRequired
    }).isRequired
  }).isRequired,
  competency: T.shape({
    id: T.number,
    name: T.string,
    description: T.string
  }),
  objective: T.shape({
    id: T.number,
    name: T.string
  }),
  progress: T.shape({
    id: T.number,
    percentage: T.number,
    level: T.shape({
      id: T.number,
      name: T.string,
      value: T.number
    })
  }),
  nbLevels: T.number,
  currentLevel: T.number,
  challenge: T.shape({
    nbPassed: T.number,
    nbToPass: T.number,
    error: T.string
  }),
  competencies: T.object,
  fetchCompetencyData: T.func.isRequired,
  resetCompetency: T.func.isRequired,
  getLevelData: T.func.isRequired,
  getRelevantResource: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    competency: state.competency.data,
    objective: state.competency.objective,
    progress: state.competency.progress,
    nbLevels: state.competency.nbLevels,
    currentLevel: state.competency.currentLevel,
    challenge: state.competency.challenge,
    competencies: state.competency.objective ? state.competencies[state.competency.objective.id] : {}
  }
}

function mapDispatchToProps(dispatch) {
  return {
    fetchCompetencyData: (objectiveId, competencyId) => {
      dispatch(actions.fetchCompetencyData(objectiveId, competencyId))
    },
    resetCompetency: () => {
      dispatch(actions.resetCompetencyData())
    },
    getLevelData: (competencyId, level) => {
      dispatch(actions.fetchLevelData(competencyId, level))
    },
    getRelevantResource: (competencyId, level) => {
      dispatch(actions.fetchRelevantResource(competencyId, level))
    }
  }
}

const ConnectedCompetencyView = withRouter(connect(mapStateToProps, mapDispatchToProps)(CompetencyView))

export {ConnectedCompetencyView as CompetencyView}