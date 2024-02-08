import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {schemeCategory20c} from '#/main/theme/color/utils'
import isEmpty from 'lodash/isEmpty'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {LineChart} from '#/main/core/layout/chart/line/components/line-chart'
import {TableData} from '#/main/app/content/list/table/components/data'
import {getNumbering} from '#/plugin/exo/resources/quiz/utils'
import {isQuestionType, getDefinition} from '#/plugin/exo/items/item-types'

/**
 * We don't use a redux store here because the chart can be rendered multiple times
 * on a quiz (2 times on the overview and 2 others on the end page) and would require
 * more code to create instantiable reducers (like for list) for this chart.
 */
class AttemptsChart extends Component {
  constructor(props) {
    super(props)

    this.state = {
      display: [],
      data: {}
    }

    this.toggleDisplay = this.toggleDisplay.bind(this)
    this.refreshData = this.refreshData.bind(this)
  }

  componentDidMount() {
    this.refreshData()
  }

  toggleDisplay(display) {
    const newDisplay = [].concat(this.state.display)

    const displayPos = newDisplay.indexOf(display)
    if (-1 === displayPos) {
      newDisplay.push(display)
    } else {
      newDisplay.splice(displayPos, 1)
    }

    this.setState({display: newDisplay})
  }

  refreshData() {
    let endpoint = ['exercise_statistics_attempts', {id: this.props.quizId}]
    if (this.props.userId) {
      endpoint = ['exercise_statistics_user_attempts', {id: this.props.quizId, userId: this.props.userId}]
    }

    fetch(url(endpoint), {
      method: 'GET' ,
      headers: new Headers({
        'Content-Type': 'application/json; charset=utf-8',
        // next header is required for symfony to recognize our requests as XMLHttpRequest
        // there is no spec about possible values, but this is the one expected by symfony
        // @see Symfony\Component\HttpFoundation\Request::isXmlHttpRequest
        'X-Requested-With': 'XMLHttpRequest'
      }),
      credentials: 'include'
    })
      .then(response => response.json())
      .then((data) => this.setState({
        display: Object.keys(data),
        data: !isEmpty(data) ? data : {}
      }))
  }

  render() {
    const chartData = [
      {
        id: 'total',
        label: trans('total'),
        color: schemeCategory20c[0]
      }
    ].concat(
      ...this.props.steps.map((step, stepIndex) => step.items.filter(item => isQuestionType(item.type)).map((item, idx) => ({
        id: item.id,
        color: schemeCategory20c[(idx + 1) % schemeCategory20c.length],
        label: item.title || trans(getDefinition(item.type).name, {}, 'question_types'),
        stepIndex: stepIndex,
        index: idx
      })))
    )

    return (
      <Fragment>
        <div className="card mb-3 panel-analytics">
          <div className="card-header">
            <h5 className="card-title mb-0">
              {trans(this.props.userId ? 'attempts_chart_user' : 'attempts_chart_all', {}, 'quiz')}
            </h5>
          </div>

          <div className="card-body">
            <LineChart
              data={chartData
                .filter(data => -1 !== this.state.display.indexOf(data.id))
                .map(data => this.state.data[data.id])
              }
              xAxisLabel={{
                show: true,
                text: trans('attempts', {}, 'quiz'),
                grid: false
              }}
              yAxisLabel={{
                show: true,
                text: trans('score'),
                grid: true
              }}
              height={250}
              width={1200}
              showArea={false}
              responsive={true}
              margin={{
                left: 50,
                top: 5,
                right: 10,
                bottom: 40
              }}
              colors={chartData
                .filter(data => -1 !== this.state.display.indexOf(data.id))
                .map(data => data.color)
              }
            />
          </div>

          <TableData
            count={chartData.length}
            data={chartData}
            definition={[
              {
                name: 'color',
                label: trans('color'),
                type: 'color',
                displayed: true,
                displayable: true
              }, {
                name: 'label',
                label: trans('question', {}, 'quiz'),
                type: 'string',
                displayed: true,
                displayable: true,
                render: (item) => {
                  if ('total' === item.id) {
                    return item.label
                  }

                  const numbering = getNumbering(this.props.questionNumberingType, item.stepIndex, item.index)

                  return (
                    <Fragment>
                      {numbering &&
                        <span className="h-numbering">{numbering}</span>
                      }

                      {item.label}
                    </Fragment>
                  )
                }
              }
            ]}
            selection={{
              current: this.state.display,
              toggle: (line) => this.toggleDisplay(line.id),
              toggleAll: () => {
                if (0 === this.state.display.length) {
                  this.setState({display: Object.keys(this.state.data)})
                } else {
                  this.setState({display: []})
                }
              }
            }}
          />
        </div>
      </Fragment>
    )
  }
}

AttemptsChart.propTypes = {
  quizId: T.string.isRequired,
  userId: T.string, // If provided, the chart will only load stats for this user
  steps: T.array,
  questionNumberingType: T.string.isRequired
}

export {
  AttemptsChart
}
