import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/competency/tools/my-objectives/store'
import {Objective} from '#/plugin/competency/tools/my-objectives/components/objective'

const MainViewComponent = (props) =>
  <div>
    <div className="my-objectives-tool-title">
      {trans('my-learning-objectives', {}, 'tools')}
    </div>
    <div
      id="objectives-accordion"
      className="panel-group my-objectives-tool-content"
      role="tablist"
      aria-multiselectable="true"
    >
      {props.objectives.length > 0 ?
        props.objectives.map((o, index) =>
          <Objective
            key={index}
            index={index}
            objective={o}
            competencies={props.competencies[o.id]}
          />
        ) :
        <div className="alert alert-warning">
          {trans('info.no_my_objectives', {}, 'competency')}
        </div>
      }
    </div>
  </div>

MainViewComponent.propTypes = {
  objectives: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    progress: T.number
  })).isRequired,
  competencies: T.object
}

const MainView = connect(
  (state) => ({
    objectives: selectors.objectives(state),
    competencies: selectors.competencies(state)
  })
)(MainViewComponent)

export {MainView}