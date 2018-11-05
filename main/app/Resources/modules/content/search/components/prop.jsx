import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {Await} from '#/main/app/components/await'

import {getType} from '#/main/app/data'
import {DataType as DataTypeTypes} from '#/main/app/data/prop-types'

const SearchInput = props => {
  const isValidSearch = !props.definition.validate || !props.definition.validate(props.currentSearch, props.options)

  if (props.definition.components.search) {
    return React.createElement(props.definition.components.search, merge({}, props.options, {
      placeholder: props.placeholder,
      search: props.currentSearch,
      isValid: isValidSearch,
      disabled: props.disabled,
      updateSearch: (value) => props.updateSearch(props.definition.parse(value, props.options))
    }))
  }

  return (
    <input
      type="text"
      className="data-filter form-control input-sm"
      value={props.currentSearch || ''}
      placeholder={props.placeholder}
      disabled={props.disabled}
      onChange={(e) => props.updateSearch(props.definition.parse(e.target.value, props.options))}
    />
  )
}

SearchInput.propTypes = {
  placeholder: T.string,
  definition: T.shape(
    DataTypeTypes.propTypes
  ).isRequired,
  options: T.object,
  currentSearch: T.any,
  disabled: T.bool,
  updateSearch: T.func.isRequired
}

class SearchProp extends Component {
  constructor(props) {
    super(props)

    this.state = {definition: null}
  }

  componentWillReceiveProps(nextProps) {
    if (this.props.type !== nextProps.type) {
      this.setState({definition: null})
    }
  }

  render() {
    return (
      <Await
        for={getType(this.props.type)}
        then={typeDef => this.setState({definition: typeDef})}
      >
        {this.state.definition &&
          <SearchInput {...this.props} definition={this.state.definition} />
        }
      </Await>
    )
  }
}

// todo : use the one defined in prop-types
SearchProp.propTypes = {
  type: T.string,
  placeholder: T.string,
  options: T.object,
  currentSearch: T.any,
  disabled: T.bool,
  updateSearch: T.func.isRequired
}

SearchProp.defaultProps = {
  disabled: false
}

export {
  SearchProp
}
