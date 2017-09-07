import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {TreeView} from '#/main/core/layout/treeview/treeview.jsx'
import {select} from './selectors'
import {actions} from './actions'

class OrganizationPicker extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return (
      <TreeView
        data={this.props.organizations}
        options={this.props.options}
        onChange={this.props.onChange}
      />
    )
  }
}

OrganizationPicker.propTypes = {
  organizations: T.arrayOf(T.object).isRequired,
  options: T.shape({
    name: T.string, //checkbox base name
    selected: T.array,
    selectable: T.bool, //allow checkbox selection
    collapse: T.bool //collapse the datatree

  }),
  onChange: T.func
}

function mapStateToProps(state) {
  return {
    organizations: select.organizations(state),
    options: select.options(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    onChange: (organization) => dispatch(actions.onChange(organization))
  }
}

const ConnectedOrganizationPicker = connect(mapStateToProps, mapDispatchToProps)(OrganizationPicker)

export {ConnectedOrganizationPicker as OrganizationPicker}
