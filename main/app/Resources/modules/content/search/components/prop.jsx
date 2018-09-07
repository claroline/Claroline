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
      search: props.currentSearch,
      isValid: isValidSearch,
      updateSearch: props.updateSearch
    }))
  }

  return (
    <input
      type="text"
      className="form-control input-sm"
      value={props.currentSearch || ''}
      onChange={(e) => props.updateSearch(e.target.value)}
    />
  )
}

SearchInput.propTypes = {
  definition: T.shape(
    DataTypeTypes.propTypes
  ).isRequired,
  options: T.object,
  currentSearch: T.any,
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
  options: T.object,
  currentSearch: T.any,
  updateSearch: T.func.isRequired
}

export {
  SearchProp
}
