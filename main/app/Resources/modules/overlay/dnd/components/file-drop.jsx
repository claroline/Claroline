import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {FileDropContext} from '#/main/app/overlay/dnd/file-drop-context'

function hasEventFiles(e) {
  if (e.dataTransfer.types) {
    for (let i=0; i < e.dataTransfer.types.length; i++) {
      if (e.dataTransfer.types[i] === 'Files') {
        return true
      }
    }
  }

  return false
}

class FileDrop extends Component {
  constructor(props) {
    super(props)

    this.state = {
      enabled: false
    }

    this.onDragStart = this.onDragStart.bind(this)
    this.onDragEnd = this.onDragEnd.bind(this)
  }

  componentDidMount() {
    document.addEventListener('dragover', this.onDragStart)
    document.addEventListener('dragleave', this.onDragEnd)
    document.addEventListener('drop', this.onDragEnd)
  }

  componentWillUnmount() {
    document.removeEventListener('dragover', this.onDragStart)
    document.removeEventListener('dragleave', this.onDragEnd)
    document.removeEventListener('drop', this.onDragEnd)
  }

  onDragStart(e) {
    if (hasEventFiles(e)) {
      e.preventDefault()

      this.setState({enabled: true})
    }
  }

  onDragEnd(e) {
    if (hasEventFiles(e)) {
      e.preventDefault()

      this.setState({enabled: false})
    }
  }

  render() {
    return (
      <FileDropContext.Provider value={this.state.enabled}>
        {this.props.children}
      </FileDropContext.Provider>
    )
  }
}

FileDrop.propTypes = {
  children: T.node.isRequired
}

export {
  FileDrop
}
