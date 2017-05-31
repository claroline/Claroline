import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'

import {utils} from './utils'

/**
 * utility method for building the selection array
 */
export function getReactAnswerInputs(item, onAnswer) {
  return cloneDeep(item.selections).map(selection => {
    selection.selectionId = selection.id

    return selection
  }).sort((a, b) => a.begin - b.begin)
    .map(element => {
      let elId = element.selectionId

      return {
        id: elId,
        begin: element.begin,
        end: element.end,
        component: (
          <SelectionInput
            id={elId}
            text={utils.getSelectionText(item, elId)}
            mode={item.mode}
            colors={item.colors}
            onAnswer={onAnswer}
            className={element.className || ''}
          />
        )
      }
    })
}

export class SelectionInput extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    switch(this.props.mode) {
      case 'select': {
        return (<DisplaySelectInput
          id={this.props.id}
          text={this.props.text}
          className={this.props.className}
          onAnswer={this.props.onAnswer}
        />)
      }
      case 'highlight': {
        return (<DisplayHighlightInput
          id={this.props.id}
          text={this.props.text}
          className={this.props.className}
          colors={this.props.colors}
          onAnswer={this.props.onAnswer}
        />)
      }
    }
  }
}

export class DisplayHighlightInput extends Component {
  constructor(props) {
    super(props)
  }

  changeSolution(colorId) {
    this.props.onAnswer(this.props.id, colorId)
    this.setState({answer: {colorId}})
  }

  render() {
    const spanCss = this.state ? {backgroundColor: this.props.colors.find(color => color.id === this.state.answer.colorId).code}: {}

    return (
      <span className={classes(this.props.className)}>
        <span>
          <span style={spanCss} className="selection-answer">
            {this.props.text}
          </span>
          <select
            value={this.state ? this.state.answer.colorId: ''}
            className="select-highlight"
            onChange={(e) => this.changeSolution(e.target.value)}
          >
            <option disabled value=''> -- select a color -- </option>
            {this.props.colors.map(color => {
              return (
                <option key={this.props.id + color.id} value={color.id} style={{backgroundColor: color.code}}>
                 {'\u00a0'}{'\u00a0'}{'\u00a0'}
                </option>
               )
            })}
          </select>
        </span>
      </span>
    )
  }
}

class DisplaySelectInput extends Component {
  constructor(props) {
    super(props)
    this.state = {checked: false}
  }

  onClick() {
    this.props.onAnswer(this.props.id, !this.state.checked)
    this.setState({checked: !this.state.checked})
  }

  render() {
    const cssClasses = {
      'checked-selection': this.state.checked,
      'span-selection': !this.state.checked
    }

    return (
      <span onClick={this.onClick.bind(this)} className={classes(cssClasses)}> {this.props.text} </span>
    )
  }
}

SelectionInput.propTypes = {
  mode: T.string.isRequired,
  id: T.string.isRequired,
  text: T.string.isRequired,
  className: T.string,
  onAnswer: T.func.isRequired,
  colors: T.arrayOf(T.shape({
    id: T.string.isRequired,
    code: T.string.isRequired
  }))
}

DisplaySelectInput.propTypes = {
  id: T.string.isRequired,
  text: T.string.isRequired,
  onAnswer: T.func.isRequired,
  className: T.string
}

DisplayHighlightInput.propTypes = {
  id: T.string.isRequired,
  text: T.string.isRequired,
  colors: T.arrayOf(T.shape({
    id: T.string.isRequired,
    code: T.string.isRequired
  })).isRequired,
  onAnswer: T.func.isRequired,
  className: T.string
}
