import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/core/translation'
import {Option} from '#/main/core/layout/select-plus/components/option.jsx'
import {Optgroup}  from '#/main/core/layout/select-plus/components/optgroup.jsx'
import {searchChoice, filterChoices} from '#/main/core/layout/select-plus/utils'

class Select extends Component {
  constructor(props) {
    super(props)
    this.state = {
      collapsed: true,
      selected: null
    }
    this.handleOnChange = this.handleOnChange.bind(this)
    this.handleOnClick  = this.handleOnClick.bind(this)
    this.collapse = this.collapse.bind(this)
  }
  
  handleOnChange(newValue, newLabel, event) {
    if (this.props.value !== newValue) {
      this.setState({
        selected: {
          value: newValue,
          label: newLabel
        },
        collapsed: true
      })
      this.props.onChange(newValue)
    }
    event.preventDefault()
    event.stopPropagation()
  }
  
  handleOnClick(event) {
    this.setState({collapsed: !this.state.collapsed})
    event.preventDefault()
    event.stopPropagation()
  }
  
  collapse() {
    this.setState({collapsed: true})
  }

  selectedChoice() {
    if (this.state.selected !== null) {
      return {
        placeholder: false,
        label: trans(this.state.selected.label, {}, this.props.transDomain)
      }
    }

    if (this.props.searchable && !!this.props.value) {
      let choice = searchChoice(this.props.choices, this.props.value, this.props.transDomain, true)
      if (choice.length > 0) {
        choice = choice[0]
        while(choice.choices.length > 0) {
          choice = choice.choices[0]
        }
        return {
          placeholder: false,
          label: trans(choice.label, {}, this.props.transDomain)
        }
      }
    }

    return {
      placeholder: true,
      label: trans('select_value', {}, 'platform')
    }
  }
  
  render() {
    const props = this.props
    const filteredChoices = (props.searchable && props.value) ?
      filterChoices(props.choices, props.value, props.transDomain) :
      props.choices
    const selectedChoice = this.selectedChoice()
    return (
      <div
        value={props.value}
        className={classes('form-control input-sm select-plus', props.className, selectedChoice.placeholder && 'placeholder')}
        onClick={this.handleOnClick}
        tabIndex={0}
        onBlur={this.collapse}
      >
        <div
          className="select-plus-value"
        >
          {selectedChoice.label}
        </div>
        <div
          className={classes('select-plus-options', this.state.collapsed ? 'hidden' : '')}
        >
          {filteredChoices.map(choice =>(
            choice.choices.length < 1 ?
              <Option
                key={choice.value}
                value={choice.value}
                label={choice.label}
                transDomain={props.transDomain}
                onSelect={this.handleOnChange}
              /> :
              <Optgroup
                key={choice.value}
                label={choice.label}
                choices={choice.choices}
                transDomain={props.transDomain}
                onSelect={this.handleOnChange}
              />
          ))}
        </div>
      </div>
    )
  }
}

Select.propTypes = {
  choices: T.array.isRequired,
  onChange: T.func.isRequired,
  value: T.any,
  className: T.string,
  transDomain: T.string,
  searchable: T.bool
}

Select.defaultProps = {
  transDomain: null,
  value: '',
  searchable: false
}

export {
  Select
}