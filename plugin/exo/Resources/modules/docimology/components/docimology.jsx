import React, { Component } from 'react'
import { connect } from 'react-redux'

import {actions as currentObjectActions} from './../actions/current-object'

import PageHeader from './../../components/layout/page-header.jsx'
import PageActions from './../../components/layout/page-actions.jsx'

// TODO : use barrel instead
import BarChart from './../../components/chart/bar/bar-chart.jsx'
import PieChart from './../../components/chart/pie/pie-chart.jsx'
import CircularGauge from './../../components/chart/gauge/circlular-gauge.jsx'

import ObjectSelector from './object-selector.jsx'

const T = React.PropTypes

const CountCard = props =>
  <div className="count-card panel panel-default">
    <span className={`icon ${props.icon}`}></span>
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

const GeneralStats = () =>
  <div className="general-stats row">
    <div className="col-md-3 col-xs-6">
      <CountCard label="steps" icon="fa fa-th-list" count={6} />
    </div>
    <div className="col-md-3 col-xs-6">
      <CountCard label="questions" icon="fa fa-question" count={10} />
    </div>
    <div className="col-md-3 col-xs-6">
      <CountCard label="users" icon="fa fa-user" count={24} />
    </div>
    <div className="col-md-3 col-xs-6">
      <CountCard label="papers" icon="fa fa-file" count={42} />
    </div>
  </div>

class Docimology extends Component {
  renderNoteBlock() {
    return (
      <div className="row">
        <div className="col-md-6">
          <div className="panel panel-default">
            <div className="panel-body">
              <BarChart
                data={[ 1, 0, 2, 4, 3, 10, 5, 8, 10, 15, 10, 15, 20, 18, 17, 9, 7, 2, 0, 1 ]}
                width={560}
                height={200}
              />
            </div>
          </div>
        </div>

        <div className="note-gauges col-md-6">
          <CircularGauge label="Minimum" color="#b94a48" value={3.5} max={20} width={180} size={25} />

          <CircularGauge label="Average" color="#c09853" value={11} max={20} width={180} size={25} />

          <CircularGauge label="Maximum" color="#468847" value={18} max={20} width={180} size={25} />
        </div>
      </div>
    )
  }

  render() {
    return (
      <div className="page-container">
        <PageHeader title="Docimology">
          <PageActions
            actions={[{
              icon: 'fa fa-fw fa-sign-out',
              label: 'Back to exercise',
              handleAction: () => true,
              primary: true
            }]}
          />
        </PageHeader>

        <ObjectSelector
          exercise={this.props.exercise}
          current={this.props.currentObject}
          handleSelect={this.props.selectObject}
        />
        <GeneralStats />

        <h2 className="h3">Indice de réussite</h2>

        <div className="row">
          <div className="col-md-4" style={{marginBottom: '20px'}}>
            <PieChart data={[10, 6, 3, 5]} colors={['#b94a48', '#c09853', '#468847', '#aaa']} width={380} />
          </div>

          <div className="col-md-8">
            <div className="panel panel-default">
              <div className="panel-body">

              </div>
            </div>
          </div>
        </div>

        <h2 className="h3">Répartition des notes</h2>
        {this.renderNoteBlock()}

        <h2 className="h3">Indice de difficulté</h2>
      </div>
    )
  }
}

Docimology.propTypes = {
  exercise: T.object.isRequired,
  currentObject: T.object.isRequired,
  selectObject: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    exercise: state.exercise,
    currentObject: state.currentObject
  }
}

function mapDispatchToProps(dispatch) {
  return {
    selectObject(type, id) {
      dispatch(currentObjectActions.selectObject(type, id))
    }
  }
}

export default connect(mapStateToProps, mapDispatchToProps)(Docimology)
