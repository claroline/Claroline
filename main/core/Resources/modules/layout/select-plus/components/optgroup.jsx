import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {Option} from '#/main/core/layout/select-plus/components/option.jsx'

class Optgroup extends Component {
  constructor(props) {
    super(props)
    
    this.handleOnClick = this.handleOnClick.bind(this)
  }
  
  handleOnClick(evt) {
    evt.preventDefault()
    evt.stopPropagation()
  }
  
  render() {
    const props = this.props
    return (
      <div
        className="select-plus-option-group"
        onClick={this.handleOnClick}
      >
        <div className="select-plus-option-group-label">
          { props.transDomain ?
            trans(props.label, {}, props.transDomain) :
            props.label
          }
        </div>
        {props.choices.map(choice =>(
          choice.choices.length < 1 ?
            <Option
              key={choice.value}
              value={choice.value}
              label={choice.label}
              transDomain={props.transDomain}
              onSelect={props.onSelect}
            /> :
            <Optgroup
              key={choice.value}
              label={choice.label}
              choices={choice.choices}
              transDomain={props.transDomain}
              onSelect={props.onSelect}
            />
        ))}
      </div>
    )
  }
}

Optgroup.propTypes = {
  choices: T.array.isRequired,
  label: T.string.isRequired,
  onSelect: T.func.isRequired,
  transDomain: T.string
}

Optgroup.defaultProps = {
  transDomain: null
}

export {
  Optgroup
}