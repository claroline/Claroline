import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {Select} from '#/main/core/layout/form/components/field/select.jsx'

class CascadeSelect  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      choices: {},
      selected: [],
      levelMax: 0
    }
  }

  componentDidMount() {
    this.initializeChoices()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.selectedValue !== this.props.selectedValue) {
      this.initializeChoices()
    }
  }

  getLevel(option) {
    let level = 0

    while (option.parent) {
      ++level
      option = option.parent
    }

    return level
  }

  initializeChoices() {
    const choices = {}
    const selected = []
    let levelMax = 0
    this.props.options.forEach(option => {
      const o = Object.assign({}, option, {value: option.id})
      const level = this.getLevel(o)

      if (level > levelMax) {
        levelMax = level
      }

      if (level === 0) {
        if (!choices[level]) {
          choices[level] = []
        }
        choices[level].push(o)
      } else {
        const parentId = o.parent.id

        if (!choices[level]) {
          choices[level] = {}
        }
        if (!choices[level][parentId]) {
          choices[level][parentId] = []
        }
        choices[level][parentId].push(o)
      }
    })
    let previousValue = 0
    this.props.selectedValue.forEach((v, level) => {
      if (level === 0) {
        const choice = choices[level].find(c => c.label === v)
        selected[level] = choice ? choice.value : ''
        previousValue = selected[level]
      } else {
        let value = ''

        if (previousValue !== '' &&  choices[level] && choices[level][previousValue]) {
          const choice = choices[level][previousValue].find(c => c.label === v)
          value = choice ? choice.value : ''
          previousValue = value
        }
        selected[level] = value
      }
    })
    this.setState({choices: choices, selected: selected, levelMax: levelMax})
  }

  convertAnswers() {
    const answers = []
    this.state.selected.forEach((s, idx) => {
      let value = ''

      if (s !== '') {
        if (idx === 0) {
          const choice = this.state.choices[idx].find(c => c.value === parseInt(s))

          if (choice) {
            value = choice.label
          }
        } else {
          const choice = this.state.choices[idx] &&
            this.state.choices[idx][this.state.selected[idx - 1]] &&
            this.state.choices[idx][this.state.selected[idx - 1]].find(c => c.value === parseInt(s))

          if (choice) {
            value = choice.label
          }
        }
      }
      answers.push(value)
    })

    return answers
  }

  onChange(level, value) {
    const selected = this.state.selected
    selected[level] = value

    if (level + 1 < selected.length) {
      selected.splice(level + 1, selected.length - (level + 1))
    }
    if (this.state.choices[level + 1] && this.state.choices[level + 1][value]) {
      selected[level + 1] = ''
    }
    this.setState({selected: selected}, () => this.props.onChange(this.convertAnswers()))
  }

  render() {
    return (
      <fieldset className="cascade-select">
        {this.state.choices[0] &&
          <Select
            options={this.state.choices[0]}
            selectedValue={this.state.selected[0] || ''}
            disabled={this.props.disabled}
            onChange={(value) => this.onChange(0, value)}
          />
        }
        {this.state.selected && Object.values(this.state.selected).map((v, idx) =>
          this.state.choices[idx + 1] && this.state.choices[idx + 1][v] ?
            <Select
              key={`select-level-${idx}`}
              options={this.state.choices[idx + 1][v]}
              selectedValue={this.state.selected[idx + 1] || ''}
              disabled={this.props.disabled}
              onChange={(value) => this.onChange(idx + 1, value)}
            /> :
            ''
        )}
      </fieldset>
    )
  }
}

CascadeSelect.propTypes = {
  options: T.arrayOf(T.shape({
    value: T.string.isRequired,
    label: T.string.isRequired,
    parent: T.shape({
      id: T.number.isRequired,
      label: T.string.isRequired
    })
  })).isRequired,
  selectedValue: T.array.isRequired,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  CascadeSelect
}
