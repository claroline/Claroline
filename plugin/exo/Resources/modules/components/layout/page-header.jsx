import React, { Component } from 'react'

const T = React.PropTypes

export default class PageHeader extends Component {
  render() {
    return (
      <header className="page-header">
        <h1>
          {this.props.title}
          &nbsp;
          {null !== this.props.subtitle && <small>{this.props.subtitle}</small>}
        </h1>

        {this.props.children}
      </header>
    )
  }
}

PageHeader.propTypes = {
  title: T.node.isRequired,
  subtitle: T.string,
  children: T.node
}

PageHeader.defaultProps = {
  subtitle: null,
  children: []
}
