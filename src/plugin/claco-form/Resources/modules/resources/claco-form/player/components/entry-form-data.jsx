import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {SelectInput} from '#/main/core/layout/form/components/field/select-input.jsx'

const EntryDataList = props =>
  <span className="entry-form-infos-list">
    {props.data.map(d =>
      <div key={d.id} className="btn-group margin-right-sm margin-bottom-sm">
        <button
          className="btn btn-default"
          type="button"
        >
          {d.name}
        </button>
        <button
          className="btn btn-danger"
          type="button"
          onClick={() => props.removeData(d)}
        >
          <span className="fa fa-times-circle" />
        </button>
      </div>
    )}
  </span>

EntryDataList.propTypes = {
  data: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })).isRequired,
  removeData: T.func.isRequired
}

class EntryFormData extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showForm: false,
      current: ''
    }
  }

  addData() {
    if (this.state.current) {
      let newChoice = {
        id: makeId(),
        name: this.state.current
      }
      const choice = this.props.choices.find(c => c.id === this.state.current)

      if (choice) {
        newChoice = choice
      } else {
        const nameChoice = this.props.choices.find(c => c.name === this.state.current)

        if (nameChoice) {
          newChoice = nameChoice
        }
      }
      this.props.onAdd(newChoice)
      this.setState({current: '', showForm: false})
    }
  }

  render() {
    return (
      <div>
        {this.props.data.length > 0 &&
          <EntryDataList
            data={this.props.data}
            removeData={data => this.props.onRemove(data)}
          />
        }
        {this.state.showForm ?
          <SelectInput
            selectMode={!this.props.allowNew}
            options={this.props.choices.map(choice => {
              return {value: choice.id, label: choice.name}
            })}
            primaryLabel={trans('add', {}, 'actions')}
            disablePrimary={!this.state.current}
            typeAhead={this.props.allowNew}
            value={this.state.current}
            onChange={value => this.setState({current: value})}
            onPrimary={this.addData.bind(this)}
            onSecondary={() => {
              this.setState({showForm: false, current: ''})
            }}
          /> :
          <button
            className="btn btn-default"
            onClick={() => this.setState({showForm: true, current: ''})}
          >
            <span className="fa fa-fw fa-plus" />
          </button>
        }
      </div>
    )
  }
}

EntryFormData.propTypes = {
  choices: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  data: T.arrayOf(T.shape({
    id: T.string.isRequired,
    name: T.string.isRequired
  })),
  allowNew: T.bool.isRequired,
  onAdd: T.func.isRequired,
  onRemove: T.func.isRequired
}

EntryFormData.defaultProps = {
  choices: [],
  data: [],
  allowNew: false
}

export {
  EntryFormData
}