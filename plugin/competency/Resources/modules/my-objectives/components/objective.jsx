import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {Competency} from './competency.jsx'

export class Objective extends Component {
  render() {
    return (
      <div className="panel panel-default">
        <div
          id={`heading-${this.props.objective.id}`}
          className="panel-heading objective-heading"
          role="tab"
        >
          <h4 className="panel-title">
            <i className="fa fa-square-o"></i>
            &nbsp;
            <a
              role="button"
              data-toggle="collapse"
              data-parent="#objectives-accordion"
              href={`#objective-${this.props.objective.id}`}
              aria-expanded="true"
              aria-controls={`objective-${this.props.objective.id}`}
            >
              <b>{this.props.objective.name}</b>
            </a>
          </h4>
          <div className="progress">
            <div
              className="progress-bar"
              role="progressbar"
              aria-valuenow={this.props.objective.progress}
              aria-valuemin="0"
              aria-valuemax="100"
              style={{width: `${this.props.objective.progress}%`}}
            >
              {this.props.objective.progress}%
            </div>
          </div>
        </div>
        <div
          id={`objective-${this.props.objective.id}`}
          className={classes('panel-collapse collapse', {'in': this.props.index === 0})}
          role="tabpanel"
          aria-labelledby={`heading-${this.props.objective.id}`}
        >
          <div className="panel-body">
            <div className="competencies-box">
              {Object.keys(this.props.competencies).map(competencyId =>
                <Competency
                  key={competencyId}
                  competency={this.props.competencies[competencyId]}
                  objective={this.props.objective}
                />
              )}
            </div>
          </div>
        </div>
      </div>
    )
  }
}

Objective.propTypes = {
  index: T.number.isRequired,
  objective: T.shape({
    id: T.number,
    name: T.string,
    progress: T.number
  }).isRequired,
  competencies: T.oneOfType([
    T.object,
    T.array
  ])
}