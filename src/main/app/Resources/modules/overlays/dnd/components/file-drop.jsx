import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {FileDropContext} from '#/main/app/overlays/dnd/file-drop-context'

function hasEventFiles(ev) {
  /*console.log(e.dataTransfer.files)
  if (e.dataTransfer.files && e.dataTransfer.files[0]) {
    return true
  }

  return false*/

  let files = []
  if (ev.dataTransfer.items) {
    // Use DataTransferItemList interface to access the file(s)
    [...ev.dataTransfer.items].forEach((item) => {
      // If dropped items aren't files, reject them
      if ('file' === item.kind) {
        files.push(files)
      }
    })
  } else {
    // Use DataTransfer interface to access the file(s)
    files = [...ev.dataTransfer.files]
  }

  return 0 !== files.length
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
    console.log('mount')
    document.addEventListener('dragenter', this.onDragStart)
    document.addEventListener('dragleave', this.onDragEnd)
    document.addEventListener('drop', this.onDragEnd)
  }

  componentWillUnmount() {
    document.removeEventListener('dragenter', this.onDragStart)
    document.removeEventListener('dragleave', this.onDragEnd)
    document.removeEventListener('drop', this.onDragEnd)
  }

  onDragStart(e) {
    console.log(e)
    if (hasEventFiles(e)) {
      e.preventDefault()

      this.setState({enabled: true})
      console.log('coucou start')
    }
  }

  onDragEnd(e) {
    console.log(e)
    if (hasEventFiles(e)) {
      e.preventDefault()

      this.setState({enabled: false})

      console.log('coucou end')
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
