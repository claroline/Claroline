import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

const ChoicesTypeAhead = props => {
  return (
    <ul className="choices-dropdown-menu dropdown-menu">
      {props.isFetching &&
        <li className="tags-fetching text-center">
          <span className="fa fa-fw fa-circle-o-notch fa-spin" />
        </li>
      }
      {props.choices.map((choice, idx) =>
        <li key={idx}>
          <a
            role="button"
            href=""
            onClick={(e) => {
              e.preventDefault()
              props.onSelect(choice)
            }}
          >
            {choice}
          </a>
        </li>
      )}
    </ul>
  )
}

ChoicesTypeAhead.propTypes = {
  choices: T.arrayOf(T.string),
  isFetching: T.bool,
  onSelect: T.func
}

class SelectInput extends Component {
  constructor(props) {
    super(props)
    this.state = {
      results: []
    }
  }

  generateTypeAhead(value) {
    if (this.props.typeAhead && this.props.options.length > 0) {
      let results = []

      if (value) {
        results = this.props.options.filter(o => {
          const regex = new RegExp(value.toUpperCase(), 'g')

          return o.label.toUpperCase().match(regex)
        }).map(o => o.label)
      }
      this.setState({results: results})
    }
  }

  render() {
    return (
      <fieldset>
        <div className="input-group">
          {this.props.selectMode ?
            <select
              className="form-control"
              defaultValue={this.props.value}
              onChange={e => this.props.onChange(e.target.value)}
            >
              {this.props.withEmptyOption &&
                <option value={this.props.emptyValue} />
              }
              {this.props.options.map((o, idx) =>
                <option key={`select-input-option-${idx}`} value={o.value}>
                  {o.label}
                </option>
              )}
            </select> :
            <input
              type="text"
              className="form-control"
              value={this.props.value}
              onChange={e => {
                this.generateTypeAhead(e.target.value)
                this.props.onChange(e.target.value)
              }}
            />
          }
          <span className="input-group-btn">
            <button
              type="button"
              className="btn btn-primary"
              dangerouslySetInnerHTML={{__html: this.props.primaryLabel}}
              disabled={this.props.disablePrimary}
              onClick={() => {
                this.setState({results: []})
                this.props.onPrimary()
              }}
            >
            </button>
            <button
              type="button"
              className="btn btn-default"
              dangerouslySetInnerHTML={{__html: this.props.secondaryLabel}}
              disabled={this.props.disableSecondary}
              onClick={() => {
                this.setState({results: []})
                this.props.onSecondary()
              }}
            >
            </button>
          </span>
        </div>
        {!this.props.selectMode && this.props.typeAhead && this.state.results.length > 0 &&
          <ChoicesTypeAhead
            choices={this.state.results}
            onSelect={(value) => {
              this.setState({results: []})
              this.props.onChange(value)
            }}
          />
        }
      </fieldset>
    )
  }
}

SelectInput.propTypes = {
  options: T.arrayOf(T.shape({
    value: T.any.isRequired,
    label: T.string.isRequired
  })).isRequired,
  selectMode: T.bool.isRequired,
  withEmptyOption: T.bool.isRequired,
  emptyValue: T.any.isRequired,
  value: T.any,
  primaryLabel: T.string.isRequired,
  secondaryLabel: T.string.isRequired,
  typeAhead: T.bool.isRequired,
  onChange: T.func.isRequired,
  onPrimary: T.func.isRequired,
  onSecondary: T.func.isRequired,
  disablePrimary: T.bool.isRequired,
  disableSecondary: T.bool.isRequired
}

SelectInput.defaultProps = {
  options: [],
  selectMode: false,
  withEmptyOption: true,
  emptyValue: '',
  primaryLabel: trans('ok'),
  secondaryLabel: trans('cancel', {}, 'actions'),
  typeAhead: false,
  disablePrimary: false,
  disableSecondary: false,
  onChange: () => {},
  onPrimary: () => {},
  onSecondary: () => {}
}

export {
  SelectInput
}
