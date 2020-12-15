import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Competency} from '#/plugin/competency/tools/my-objectives/components/competency'

const Objective = (props) =>
  <div className="panel panel-default">
    <div
      id={`heading-${props.objective.id}`}
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
          href={`#objective-${props.objective.id}`}
          aria-expanded="true"
          aria-controls={`objective-${props.objective.id}`}
        >
          <b>{props.objective.name}</b>
        </a>
      </h4>
      <div className="progress">
        <div
          className="progress-bar"
          role="progressbar"
          aria-valuenow={props.objective.progress}
          aria-valuemin="0"
          aria-valuemax="100"
          style={{width: `${props.objective.progress}%`}}
        >
          {props.objective.progress}%
        </div>
      </div>
    </div>
    <div
      id={`objective-${props.objective.id}`}
      className={classes('panel-collapse collapse', {'in': props.index === 0})}
      role="tabpanel"
      aria-labelledby={`heading-${props.objective.id}`}
    >
      <div className="panel-body">
        <div className="competencies-box">
          {Object.keys(props.competencies).map(competencyId =>
            <Competency
              key={competencyId}
              competency={props.competencies[competencyId]}
              objective={props.objective}
            />
          )}
        </div>
      </div>
    </div>
  </div>

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

export {
  Objective
}