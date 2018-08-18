import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {trans} from '#/main/core/translation'
import {Await} from '#/main/app/components/await'
import {FormData} from '#/main/app/content/form/containers/data'

import {getFile} from '#/main/core/files'
import {selectors} from '#/main/core/resources/file/editor/store'

// TODO : find a way to make this kind of component generic (duplicated for all apps coming from dynamic loading)
// TODO : find a way to reuse file creation form component

class Editor extends Component {
  constructor(props) {
    super(props)

    this.state = {
      fileEditor: null,
      fileStyles: null
    }
  }

  render() {
    return (
      <FormData
        level={5}
        name={selectors.FORM_NAME}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'file',
                label: trans('file'),
                type: 'file',
                required: true,
                options: {
                  //unzippable: true
                }
              }
            ]
          }
        ]}
      >
        <Await
          for={getFile(this.props.mimeType)()}
          then={module => this.setState({
            fileEditor: get(module, 'fileType.components.editor') || null,
            fileStyles: get(module, 'fileType.styles') || null
          })}
        >
          <div>
            {this.state.fileEditor && React.createElement(this.state.fileEditor, {
              file: this.props.file
            })}

            {this.state.fileStyles &&
              <link rel="stylesheet" type="text/css" href={theme(this.state.fileStyles)} />
            }
          </div>
        </Await>
      </FormData>
    )
  }
}

Editor.propTypes = {
  mimeType: T.string.isRequired,
  file: T.shape({

  }).isRequired
}

const FileEditor = connect(
  (state) => ({
    mimeType: selectors.mimeType(state),
    file: selectors.file(state)
  })
)(Editor)

export {
  FileEditor
}
