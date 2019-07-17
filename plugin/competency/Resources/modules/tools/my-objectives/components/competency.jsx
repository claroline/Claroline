import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions} from '#/plugin/competency/tools/my-objectives/store'

class CompetencyComponent extends Component {
  getProgressPercentage() {
    return this.props.competency.userLevelValue !== undefined ?
      Math.round(((this.props.competency.userLevelValue + 1) / (this.props.competency.requiredLevel + 1)) * 100) :
      0
  }

  getLevelsStars() {
    return this.props.competency.userLevelValue === undefined ? 0 : this.props.competency.userLevelValue + 1
  }

  getRemainingLevelsStars() {
    return this.props.competency.userLevelValue === undefined ?
      this.props.competency.requiredLevel + 1 :
      this.props.competency.requiredLevel > this.props.competency.userLevelValue ?
        this.props.competency.requiredLevel - this.props.competency.userLevelValue :
        0
  }

  startCompetency() {
    this.props.getRelevantResource(this.props.competency.id, this.props.competency.nbLevels - 1)
  }

  render() {
    return (
      <div className="competency-box">
        <div className="box-header">
          <div className="box-title">
            {this.props.competency.name}
          </div>
        </div>
        <div className="box-content">
          {!this.props.competency.userLevelValue && !this.props.competency.latestResource &&
            <div className="competency-box-content">
              <i className="fa fa-line-chart fa-5x" aria-hidden="true"></i>
              {this.props.competency.error ?
                <div className="alert alert-danger">
                  {this.props.competency.error}
                </div> :
                <a className="btn btn-primary" onClick={() => this.startCompetency()}>
                  {trans('objective.evaluate', {}, 'competency')}
                </a>
              }
            </div>
          }
          {this.props.competency.userLevelValue !== undefined && this.props.competency.userLevelValue >= this.props.competency.requiredLevel &&
            <div className="competency-box-content">
              <div className="acquired-content">
                <i className="fa fa-check-square-o fa-5x" aria-hidden="true"></i>
                <h4>{trans('competency.acquired', {}, 'competency')}</h4>
              </div>
              <a href={`${this.props.path}/${this.props.objective.id}/competency/${this.props.competency.id}`} className="btn btn-primary">
                {trans('objective.train', {}, 'competency')}
              </a>
            </div>
          }
          {((this.props.competency.userLevelValue && this.props.competency.userLevelValue < this.props.competency.requiredLevel) ||
          (!this.props.competency.userLevelValue && this.props.competency.latestResource)) &&
            <div className="competency-box-content">
              <div className="level">
                {[...Array(this.getLevelsStars())].map((x, index) =>
                  <i key={index} className="fa fa-star fa-2x" aria-hidden="true"></i>
                )}
                {[...Array(this.getRemainingLevelsStars())].map((x, index) =>
                  <i key={index} className="fa fa-star-o fa-2x" aria-hidden="true"></i>
                )}
              </div>
              <div className={`c100 p${this.getProgressPercentage()} small`}>
                <span>{this.getProgressPercentage()}%</span>
                <div className="slice">
                  <div className="bar"></div>
                  <div className="fill"></div>
                </div>
              </div>
              <a href={`${this.props.path}/${this.props.objective.id}/competency/${this.props.competency.id}`} className="btn btn-primary">
                {trans('objective.continue', {}, 'competency')}
              </a>
            </div>
          }
        </div>
      </div>
    )
  }
}

CompetencyComponent.propTypes = {
  path: T.string.isRequired,
  objective: T.shape({
    id: T.number,
    name: T.string
  }).isRequired,
  competency: T.shape({
    id: T.number,
    name: T.string,
    progress: T.number,
    userLevelValue: T.number,
    requiredLevel: T.number,
    nbLevels: T.number,
    latestResource: T.number,
    error: T.string
  }).isRequired,
  getRelevantResource: T.func.isRequired
}

const Competency = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    getRelevantResource(competencyId, level) {
      dispatch(actions.fetchRelevantResource(competencyId, level))
    }
  })
)(CompetencyComponent)

export {Competency}