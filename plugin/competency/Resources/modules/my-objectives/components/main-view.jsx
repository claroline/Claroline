import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {withRouter} from 'react-router-dom'
import {trans} from '#/main/core/translation'
import {Objective} from './objective.jsx'

class MainView extends Component {
  render() {
    return (
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
          {this.props.objectives.length > 0 ?
            this.props.objectives.map((o, index) =>
              <Objective
                key={index}
                index={index}
                objective={o}
                competencies={this.props.competencies[o.id]}
              />
            ) :
            <div className="alert alert-warning">
              {trans('info.no_my_objectives', {}, 'competency')}
            </div>
          }
        </div>
      </div>
    )
  }
}

MainView.propTypes = {
  objectives: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    progress: T.number
  })).isRequired,
  competencies: T.object
}

function mapStateToProps(state) {
  return {
    objectives: state.objectives,
    competencies: state.competencies
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedMainView = withRouter(connect(mapStateToProps, mapDispatchToProps)(MainView))

export {ConnectedMainView as MainView}