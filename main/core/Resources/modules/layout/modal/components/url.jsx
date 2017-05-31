import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {BaseModal} from './base.jsx'

/**
 * Displays a modal rendered from the server.
 */
class UrlModal extends Component {
  constructor(props) {
    super(props)

    this.id = Math.random().toString(36).replace(/[^a-z]+/g, '').substr(0, 5)

    this.state = {
      isFetching: true,
      content: null
    }

    fetch(this.props.url, {method: 'GET', credentials: 'include'})
      .then(response => response.text())
      .then(text => this.onModalLoaded(text))
  }

  render() {
    return (
    <BaseModal
      {...this.props}
      show={this.props.show && !this.state.isFetching}
    >
      <div
        id={this.id}
        dangerouslySetInnerHTML={{__html: this.state.content}}
      />
    </BaseModal>
    )
  }

  submitForm() {
    const form = document.querySelector(`#${this.id} form`)
    const url = form.action

    fetch(url, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'include'
    }).then(data => {
      this.props.hideModal(data)
    })
  }

  onModalLoaded(content) {
    this.setState({isFetching: false, content: content})
    document.querySelector(`#${this.id} button[type="submit"]`).addEventListener('click', event => {
      event.preventDefault()
      this.submitForm()
    })

    document.querySelector(`#${this.id}`).addEventListener('keypress', event => {
      if (event.keyCode === 13 && event.target.nodeName !== 'TEXTAREA') {
        event.preventDefault()
        this.submitForm()
      }
    })

    const array = []
    const nodes = document.querySelectorAll(`#${this.id} [data-dismiss="modal"]`)

    array.forEach.call(nodes, node => node.addEventListener('click', () => this.props.hideModal()))
  }
}

UrlModal.propTypes = {
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  show: T.bool.isRequired,
  url: T.string.isRequired
}

// required when testing proptypes on code instrumented by istanbul
// @see https://github.com/facebook/jest/issues/1824#issuecomment-250478026
UrlModal.displayName = 'UrlModal'

export {
  UrlModal
}
