import React from 'react'
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

const SearchProp = props =>
  <Await
    for={getType(props.type)}
    then={(definition) => (
      <SearchInput {...props} definition={definition} />
    )}
  />

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
